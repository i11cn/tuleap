<?php
/**
 * Copyright (c) Enalean, 2015. All rights reserved
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/
 */

class MediawikiAdminPanePresenter {

    /** @var Project */
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function admin_title()
    {
        return $GLOBALS['Language']->getText('plugin_mediawiki', 'admin_title');
    }

    public function permissions_label()
    {
        return $GLOBALS['Language']->getText('plugin_mediawiki', 'permissions_label');
    }

    public function language_label()
    {
        return $GLOBALS['Language']->getText('plugin_mediawiki', 'language_label');
    }

    public function group_id()
    {
        return $this->project->getID();
    }
}