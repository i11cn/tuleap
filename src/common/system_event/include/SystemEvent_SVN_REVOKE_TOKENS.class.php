<?php
/**
 * Copyright Enalean (c) 2015. All rights reserved.
 *
 * Tuleap and Enalean names and logos are registrated trademarks owned by
 * Enalean SAS. All other trademarks or names are properties of their respective
 * owners.
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

class SystemEvent_SVN_REVOKE_TOKENS extends SystemEvent {

    /** @var BackendSVN */
    private $backend_svn;

    public function injectDependencies(BackendSVN $backend_svn)
    {
        $this->backend_svn = $backend_svn;
    }

    public function process()
    {
        if ($this->getParameters()) {
            $this->backend_svn->setSVNApacheConfNeedUpdate();
            $this->done();
        } else {
            $this->error('No project id provided');
        }
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
        return 'projects: ' . $this->getParameters();
    }
}