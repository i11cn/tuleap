<?php
/**
 * Copyright Enalean (c) 2016 - Present. All rights reserved.
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
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
 *
 *
 */

use Tuleap\admin\ProjectCreation\ProjectVisibility\ProjectVisibilityConfigManager;
use Tuleap\Project\UserRemover;
use Tuleap\SVN\SVNAuthenticationCacheInvalidator;

/**
* System Event classes
*
*/
class SystemEvent_PROJECT_IS_PRIVATE extends SystemEvent
{
    /**
     * @var SVNAuthenticationCacheInvalidator
     */
    private $svn_authentication_cache_invalidator;
    /**
     * @var UserRemover
     */
    private $user_remover;
    /**
     * @var UGroupManager
     */
    private $ugroup_manager;

    public function injectDependencies(
        SVNAuthenticationCacheInvalidator $svn_authentication_cache_invalidator,
        UserRemover $user_remover,
        UGroupManager $ugroup_manager
    ) {
        $this->svn_authentication_cache_invalidator = $svn_authentication_cache_invalidator;
        $this->user_remover                         = $user_remover;
        $this->ugroup_manager                       = $ugroup_manager;
    }

    /**
     * Verbalize the parameters so they are readable and much user friendly in
     * notifications
     *
     * @param bool $with_link true if you want links to entities. The returned
     * string will be html instead of plain/text
     *
     * @return string
     */
    public function verbalizeParameters($with_link)
    {
        $txt = '';
        list($group_id, $project_is_private) = $this->getParametersAsArray();
        $txt .= 'project: '. $this->verbalizeProjectId($group_id, $with_link) .', project is private: '. ($project_is_private ? 'true' : 'false');
        return $txt;
    }

    /**
     * Process stored event
     */
    public function process()
    {
        list($group_id, $project_is_private) = $this->getParametersAsArray();

        $project = $this->getProject($group_id);
        if ($project === null) {
            return false;
        }

        $this->cleanRestrictedUsersIfNecessary($project);

        if ($project->usesCVS()) {
            if (!Backend::instance('CVS')->setCVSPrivacy($project, $project_is_private)) {
                $this->error("Could not set cvs privacy for project $group_id");
                return false;
            }
        }

        if ($project->usesSVN()) {
            $backendSVN    = Backend::instance('SVN');
            if (!$backendSVN->setSVNPrivacy($project, $project_is_private)) {
                $this->error("Could not set svn privacy for project $group_id");
                return false;
            }
            if (!$backendSVN->updateSVNAccess($group_id, $project->getSVNRootPath()) ) {
                $this->error("Could not update svn access file for project $group_id");
                return false;
            }
        }

        $should_notify_project_members = (bool) ForgeConfig::get(
            ProjectVisibilityConfigManager::SEND_MAIL_ON_PROJECT_VISIBILITY_CHANGE
        );

        if ($should_notify_project_members) {
            $this->notifyProjectMembers($project);
        }

        //allows to link plugins to this system event
        $this->callSystemEventListeners( self::class );

        $this->done();

        return true;
    }

    private function cleanRestrictedUsersIfNecessary(Project $project) : void
    {
        if (! ForgeConfig::areRestrictedUsersAllowed() || $project->getAccess() !== Project::ACCESS_PRIVATE_WO_RESTRICTED) {
            return;
        }
        $project_members = $project->getMembers();
        foreach ($project_members as $project_member) {
            if ($project_member->isRestricted()) {
                $this->user_remover->removeUserFromProject($project->getID(), $project_member->getId());
            }
        }

        $static_ugroups = $this->ugroup_manager->getStaticUGroups($project);
        foreach ($static_ugroups as $static_ugroup) {
            $ugroup_members = $static_ugroup->getMembers();
            foreach ($ugroup_members as $ugroup_member) {
                if ($ugroup_member->isRestricted()) {
                    $static_ugroup->removeUser($ugroup_member);
                }
            }
        }
    }

    private function notifyProjectMembers(Project $project)
    {
        foreach($project->getMembers() as $member) {
            $this->notifyUser($project, $member);
        }
    }

    private function notifyUser(Project $project, PFUser $user)
    {
        $user_language = $user->getLanguage();
        $purifier = Codendi_HTMLPurifier::instance();

        $title = $user_language->getText(
            'project_privacy',
            'email_visibility_change_title',
            $project->getUnixName()
        );

        $body = $this->getBody($project, $user_language);

        $body_text = $purifier->purify($body, CODENDI_PURIFIER_STRIP_HTML);

        $mail = new Codendi_Mail();
        $mail->setFrom(ForgeConfig::get('sys_noreply'));
        $mail->setTo($user->getEmail());
        $mail->setSubject($purifier->purify($title, CODENDI_PURIFIER_STRIP_HTML));
        $mail->setBodyHtml($body_text);
        $mail->setBodyText($body_text);

        $mail->send();
    }

    private function getBody(Project $project, BaseLanguage $user_language): string
    {
        if (ForgeConfig::areRestrictedUsersAllowed()) {
            switch ($project->getAccess()) {
                case Project::ACCESS_PUBLIC:
                    return $user_language->getText(
                        'project_privacy',
                        'email_visibility_change_body_public',
                        $project->getUnconvertedPublicName()
                    );
                case Project::ACCESS_PUBLIC_UNRESTRICTED:
                    return $user_language->getText(
                        'project_privacy',
                        'email_visibility_change_body_unrestricted',
                        $project->getUnconvertedPublicName()
                    );
                case Project::ACCESS_PRIVATE_WO_RESTRICTED:
                    return $user_language->getText(
                        'project_privacy',
                        'email_visibility_change_body_private',
                        $project->getUnconvertedPublicName()
                    );
                case Project::ACCESS_PRIVATE:
                default:
                    return $user_language->getText(
                        'project_privacy',
                        'email_visibility_change_body_private_unrestricted',
                        $project->getUnconvertedPublicName()
                    );

            }
        } else {
            return $user_language->getText(
                'project_privacy',
                'email_visibility_change_body_' . $project->getAccess(),
                $project->getUnconvertedPublicName()
            );
        }
    }
}
