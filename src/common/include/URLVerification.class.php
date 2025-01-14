<?php
/**
 * Copyright (c) Enalean, 2013-Present. All Rights Reserved.
 * Copyright (c) STMicroelectronics, 2010. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

use Tuleap\BurningParrotCompatiblePageDetector;
use Tuleap\Error\ErrorDependenciesInjector;
use Tuleap\Error\PermissionDeniedPrivateProjectController;
use Tuleap\Error\PermissionDeniedRestrictedAccountController;
use Tuleap\Error\ProjectAccessSuspendedController;
use Tuleap\Error\PermissionDeniedRestrictedAccountProjectController;
use Tuleap\Error\PlaceHolderBuilder;
use Tuleap\Layout\ErrorRendering;
use Tuleap\Project\Admin\MembershipDelegationDao;
use Tuleap\Project\ProjectAccessChecker;
use Tuleap\Project\ProjectAccessSuspendedException;
use Tuleap\Project\RestrictedUserCanAccessUrlOrProjectVerifier;
use Tuleap\Request\RequestInstrumentation;

/**
 * Check the URL validity (protocol, host name, query) regarding server constraints
 * (anonymous, user status, project privacy, ...) and manage redirection when needed
 */
class URLVerification {

    protected $urlChunks = null;

    /**
     * Returns an array containing data for the redirection URL
     *
     * @return Array
     */
    function getUrlChunks()
    {
        return $this->urlChunks;
    }

    /**
     * Returns the current user
     *
     * @return PFUser
     */
    function getCurrentUser()
    {
        return UserManager::instance()->getCurrentUser();
    }

    /**
     * Returns an instance of EventManager
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        return EventManager::instance();
    }

    /**
     * Returns a instance of Url
     *
     * @return URL
     */
    protected function getUrl()
    {
        return new URL();
    }

    /**
     * @return PermissionsOverrider_PermissionsOverriderManager
     */
    protected function getPermissionsOverriderManager()
    {
        return PermissionsOverrider_PermissionsOverriderManager::instance();
    }

    private function getForgeAccess() : ForgeAccess
    {
        return new ForgeAccess($this->getPermissionsOverriderManager());
    }

    /**
     * Tests if the requested script name is allowed for anonymous or not
     *
     * @param Array $server
     *
     * @return bool
     */
    function isScriptAllowedForAnonymous($server)
    {
        // Defaults
        $allowedAnonymous['/account/login.php']          = true;
        $allowedAnonymous['/account/register.php']       = true;
        $allowedAnonymous['/account/change_pw.php']      = true;
        $allowedAnonymous['/include/check_pw.php']       = true;
        $allowedAnonymous['/account/lostpw.php']         = true;
        $allowedAnonymous['/account/lostlogin.php']      = true;
        $allowedAnonymous['/account/lostpw-confirm.php'] = true;
        if (isset($allowedAnonymous[$server['SCRIPT_NAME']]) && $allowedAnonymous[$server['SCRIPT_NAME']] == true) {
            return true;
        }

        if ($server['REQUEST_URI'] === '/' && ForgeConfig::get(ForgeAccess::ANONYMOUS_CAN_SEE_SITE_HOMEPAGE) === '1') {
            return true;
        }

        if ($server['REQUEST_URI'] === '/contact.php' && ForgeConfig::get(ForgeAccess::ANONYMOUS_CAN_SEE_CONTACT) === '1') {
            return true;
        }

        // Plugins
        $anonymousAllowed = false;
        $params = array('script_name' => $server['SCRIPT_NAME'], 'anonymous_allowed' => &$anonymousAllowed);
        $this->getEventManager()->processEvent('anonymous_access_to_script_allowed', $params);

        return $anonymousAllowed;
    }

    /**
     * Should we treat current request as an exception
     *
     * @param array $server
     *
     * @return bool
     */
    function isException($server)
    {
        return preg_match('`^(?:/plugins/[^/]+)?/(?:soap|api)/`', $server['SCRIPT_NAME']);
    }

    /**
     * Tests if the server name is valid or not
     *
     * @param Array $server
     * @param String $host
     *
     * @return bool
     */
    function isValidServerName($server, $host)
    {

        return ($server['HTTP_HOST'] == $host);
    }

    /**
     * Check if an URI is internal to the application or not. We reject all URLs
     * except /path/to/feature
     *
     * @return bool
     */
    public function isInternal($uri)
    {
        $url_decoded = urldecode($uri);
        return preg_match('/^\/[[:alnum:]]+/', $url_decoded) === 1;
    }

    /**
     * Returns the redirection URL from urlChunks
     *
     * This method returns the ideal URL to use to access a ressource. It doesn't
     * check if the URL is valid or not.
     * It conserves the same entree for protocol (i.e host or  request)  when it not has
     * been modified by one of the methods dedicated to verify its validity.
     *
     * @param Array $server
     *
     * @return String
     */
    function getRedirectionURL(HTTPRequest $request, $server)
    {
        $chunks   = $this->getUrlChunks($server);

        $location = $this->getRedirectLocation($request, $server, $chunks);

        if (isset($chunks['script'])) {
            $location .= $chunks['script'];
        } else {
            $location .= $server['REQUEST_URI'];
        }
        return $location;
    }

    private function getRedirectLocation(HTTPRequest $request, array $server, array $chunks)
    {
        if (isset($chunks['protocol']) || isset($chunks['host'])) {
            return $this->rewriteProtocol($request, $server, $chunks);
        }
        return '';
    }

    private function rewriteProtocol(HTTPRequest $request, array $server, array $chunks)
    {
        if (isset($chunks['protocol'])) {
            $location = $chunks['protocol']."://";
        } else {
            if ($request->isSecure()) {
                $location = "https://";
            } else {
                $location = "http://";
            }
        }

        if (isset($chunks['host'])) {
            $location .= $chunks['host'];
        } else {
            $location .= $server['HTTP_HOST'];
        }

        return $location;
    }

    /**
     * Modify the protocol entry if needed
     *
     * @param Array $server
     *
     * @return void
     */
    public function verifyProtocol(HTTPRequest $request)
    {
        if (! $request->isSecure() && ForgeConfig::get('sys_https_host')) {
            $this->urlChunks['protocol'] = 'https';
            $this->urlChunks['host']     = ForgeConfig::get('sys_https_host');
        }
    }

    /**
     * Check if anonymous is granted to access else redirect to login page
     *
     * @param Array $server
     *
     * @return void
     */
    public function verifyRequest($server)
    {
        $user = $this->getCurrentUser();

        if (
            $this->getForgeAccess()->doesPlatformRequireLogin() &&
            $user->isAnonymous() &&
            ! $this->isScriptAllowedForAnonymous($server)
        ) {
            $redirect = new URLRedirect($this->getEventManager());
            $this->urlChunks['script']   = $redirect->buildReturnToLogin($server);
        }
    }

    /**
     * Checks that a restricted user can access the requested URL.
     *
     * @param Array $server
     *
     * @return void
     */
    function checkRestrictedAccess($server)
    {
        $user = $this->getCurrentUser();
        if ($user->isRestricted()) {
            $url = $this->getUrl();
            if (!$this->restrictedUserCanAccessUrl($user, $url, $server['REQUEST_URI'], null)) {
                $this->displayRestrictedUserError($user);
            }
        }
    }

    /**
     * Test if given url is restricted for user
     *
     * @param PFUser $user
     * @param Url $url
     * @param String $request_uri
     * @return bool False if user not allowed to see the content
     */
    protected function restrictedUserCanAccessUrl(PFUser $user, URL $url, string $request_uri, ?Project $project = null)
    {
        $verifier = new RestrictedUserCanAccessUrlOrProjectVerifier($this->getEventManager(), $url, $request_uri);

        return $verifier->isRestrictedUserAllowedToAccess($user, $project);
    }

    /**
     * Display error message for restricted user in a project
     *
     * @protected for test purpose
     *
     * @param URL $url Accessed url
     *
     * @return void
     */
    protected function displayRestrictedUserProjectError(PFUser $user, Project $project)
    {
        $GLOBALS['Response']->send401UnauthorizedHeader();
        $controller = new PermissionDeniedRestrictedAccountProjectController(
            $this->getThemeManager(),
            new ErrorDependenciesInjector(),
            new PlaceHolderBuilder(ProjectManager::instance())
        );
        $controller->displayError($user, $project);
        exit;
    }

    /**
     * Display error message for restricted user.
     *
     * @protected for test purpose
     *
     * @param URL $url Accessed url
     *
     * @return void
     */
    protected function displayRestrictedUserError(PFUser $user)
    {
        $GLOBALS['Response']->send401UnauthorizedHeader();
        $controller = new PermissionDeniedRestrictedAccountController(
            $this->getThemeManager(),
            new ErrorDependenciesInjector(),
            new PlaceHolderBuilder(ProjectManager::instance())
        );
        $controller->displayError($user);
        exit;
    }

    public function displayPrivateProjectError(PFUser $user, ?Project $project = null)
    {
        $GLOBALS['Response']->send401UnauthorizedHeader();

        $this->checkUserIsLoggedIn($user);

        $sendMail = new PermissionDeniedPrivateProjectController(
            $this->getThemeManager(),
            new PlaceHolderBuilder(ProjectManager::instance()),
            new ErrorDependenciesInjector()
        );
        $sendMail->displayError($user, $project);
        exit;
    }

    public function displaySuspendedProjectError(PFUser $user, Project $project)
    {
        $GLOBALS['Response']->send401UnauthorizedHeader();

        $this->checkUserIsLoggedIn($user);

        $suspended_project_controller = new ProjectAccessSuspendedController(
            $this->getThemeManager()
        );

        $suspended_project_controller->displayError($user);
        exit;
    }

    /**
     * Check URL is valid and redirect to the right host/url if needed.
     *
     * Force SSL mode if required except if request comes from localhost, or for api scripts
     *
     * Limit responsability of each method for sake of simplicity. For instance:
     * getRedirectionURL will not check all the server name or script name details
     * (localhost, api, etc). It only cares about generating the right URL.
     *
     * @param Array $server
     *
     * @return void
     */
    public function assertValidUrl($server, HTTPRequest $request, ?Project $project = null)
    {
        if (!$this->isException($server)) {
            $this->verifyProtocol($request);
            $this->verifyRequest($server);
            $chunks = $this->getUrlChunks();
            if (isset($chunks)) {
                $location = $this->getRedirectionURL($request, $server);
                $this->header($location);
            }

            $user = $this->getCurrentUser();
            $url  = $this->getUrl();
            try {
                if (! $user->isAnonymous()) {
                    $password_expiration_checker = new User_PasswordExpirationChecker();
                    $password_expiration_checker->checkPasswordLifetime($user);
                }

                if (! $project) {
                    $group_id = (isset($GLOBALS['group_id'])) ? $GLOBALS['group_id'] : $url->getGroupIdFromUrl($server['REQUEST_URI']);
                    if ($group_id) {
                        $project = $this->getProjectManager()->getProject($group_id);
                    }
                }
                if ($project) {
                    $this->userCanAccessProject($user, $project);
                } else {
                    $this->checkRestrictedAccess($server);
                }

                return true;

            } catch (Project_AccessRestrictedException $exception) {
                if (! isset($project)) {
                    $project = null;
                }
                $this->displayRestrictedUserProjectError($user, $project);
            } catch (Project_AccessPrivateException $exception) {
                if (! isset($project)) {
                    $project = null;
                }
                $this->displayPrivateProjectError($user, $project);
            } catch (Project_AccessProjectNotFoundException $exception) {
                RequestInstrumentation::increment(404);
                (new ErrorRendering())->rendersError(
                    $this->getThemeManager()->getBurningParrot($request->getCurrentUser()),
                    $request,
                    404,
                    _('Not found'),
                    $exception->getMessage()
                );
                exit;
            } catch (Project_AccessDeletedException $exception) {
                $this->exitError(
                    $GLOBALS['Language']->getText('include_session', 'insufficient_g_access'),
                    $exception->getMessage()
                );
            } catch (ProjectAccessSuspendedException $exception) {
                $this->displaySuspendedProjectError($user, $project);
            } catch (User_PasswordExpiredException $exception) {
                if (! $this->isScriptAllowedForAnonymous($server)) {
                    $GLOBALS['Response']->addFeedback(Feedback::ERROR, $GLOBALS['Language']->getText('include_account', 'change_pwd_err'));
                    $GLOBALS['Response']->redirect('/account/change_pw.php?user_id'.$user->getId());
                }
            }
        }
    }

    /**
     * Ensure given user can access given project
     *
     * @param PFUser $user
     * @param Project $project
     * @return bool
     * @throws Project_AccessProjectNotFoundException
     * @throws Project_AccessDeletedException
     * @throws Project_AccessRestrictedException
     * @throws Project_AccessPrivateException
     * @throws ProjectAccessSuspendedException
     */
    public function userCanAccessProject(PFUser $user, Project $project)
    {
        $checker = new ProjectAccessChecker(
            $this->getPermissionsOverriderManager(),
            new RestrictedUserCanAccessUrlOrProjectVerifier($this->getEventManager(), $this->getUrl(), $_SERVER['REQUEST_URI']),
            EventManager::instance()
        );

        $checker->checkUserCanAccessProject($user, $project);

        return true;
    }

    /**
     * Ensure given user can access given project and user is admin of the project
     *
     * @param PFUser $user
     * @param Project $project
     * @return bool
     *
     * @throws Project_AccessProjectNotFoundException
     * @throws Project_AccessDeletedException
     * @throws Project_AccessRestrictedException
     * @throws Project_AccessPrivateException
     * @throws Project_AccessNotAdminException
     * @throws ProjectAccessSuspendedException
     */
    public function userCanAccessProjectAndIsProjectAdmin(PFUser $user, Project $project)
    {
        if ($this->userCanAccessProject($user, $project)) {
            if (! $user->isAdmin($project->getId())) {
                throw new Project_AccessNotAdminException();
            }
            return true;
        }
    }

    /**
     * @param PFUser $user
     * @param Project $project
     * @return bool
     *
     * @throws Project_AccessProjectNotFoundException
     * @throws Project_AccessDeletedException
     * @throws Project_AccessRestrictedException
     * @throws Project_AccessPrivateException
     * @throws Project_AccessNotAdminException
     * @throws ProjectAccessSuspendedException
     */
    public function userCanManageProjectMembership(PFUser $user, Project $project)
    {
        if ($this->userCanAccessProject($user, $project)) {
            $dao = new MembershipDelegationDao();
            if (! $user->isAdmin($project->getId()) && ! $dao->doesUserHasMembershipDelegation($user->getId(), $project->getID())) {
                throw new Project_AccessNotAdminException();
            }
            return true;
        }
    }


    /**
     * Wrapper for tests
     *
     * @param String $title Title of the error message
     * @param String $text  Text of the error message
     *
     * @return Void
     */
    function exitError($title, $text)
    {
        exit_error($title, $text);
    }

    /**
     * Wrapper for tests
     *
     * @return ProjectManager
     */
    function getProjectManager()
    {
        return ProjectManager::instance();
    }

    /**
     * Wrapper of header method
     *
     * @param String $location
     *
     * @return void
     */
    function header($location)
    {
        header('Location: '.$location);
        exit;
    }

    /**
     * @param PFUser $user
     */
    private function checkUserIsLoggedIn(PFUser $user)
    {
        if ($user->isAnonymous()) {
            $event_manager = EventManager::instance();
            $redirect = new URLRedirect($event_manager);
            $redirect->redirectToLogin();
        }
    }

    /**
     * @return ThemeManager
     */
    private function getThemeManager()
    {
        return new ThemeManager(
            new BurningParrotCompatiblePageDetector(
                new Tuleap\Request\CurrentPage(),
                new \User_ForgeUserGroupPermissionsManager(
                    new \User_ForgeUserGroupPermissionsDao()
                )
            )
        );
    }

}
