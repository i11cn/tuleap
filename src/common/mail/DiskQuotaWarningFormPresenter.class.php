<?php
/**
 * Copyright (c) STMicroelectronics 2014. All rights reserved
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

class DiskQuotaWarningFormPresenter {

    public $subject_content;
    public $body_content;
    public $csrf_token;
    public $action;
    public $title;
    public $project_id;
    public $submit_button;
    public $close_button;
    public $subject_label;
    public $body_label;

    public function __construct($project_id, CSRFSynchronizerToken $token, $subject_label, $action, $subject_content, $body_content) {
        $this->project_id      = $project_id;
        $this->action          = $action;
        $this->csrf_token      = $token->fetchHTMLInput();
        $this->subject_content = $subject_content;
        $this->body_content    = $body_content;
        $this->title           = $subject_label;
        $this->submit_button   = $GLOBALS['Language']->getText('global','btn_submit');
        $this->close_button    = $GLOBALS['Language']->getText('global','btn_cancel');
        $this->subject_label   = $GLOBALS['Language']->getText('my_index','subject_label');
        $this->body_label      = $GLOBALS['Language']->getText('my_index','body_label');
    }

    public function getTemplateDir() {
        return ForgeConfig::get('codendi_dir') .'/src/templates/project';
    }

}

?>
