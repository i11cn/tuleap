<?php
/**
 * Copyright (c) Enalean, 2011 - 2018. All Rights Reserved.
 * Copyright (c) STMicroelectronics, 2007. All Rights Reserved.
 *
 * Originally written by Manuel Vacelet, 2007
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

require_once('Docman_View_Details.class.php');
require_once('Docman_View_ItemDetailsSectionPaste.class.php');

class Docman_View_Paste extends Docman_View_Details {

    function _getTitle($params)
    {
        $hp = Codendi_HTMLPurifier::instance();
        return $GLOBALS['Language']->getText('plugin_docman', 'details_paste_title', array(
            $hp->purify($params['itemToPaste']->getTitle(), CODENDI_PURIFIER_CONVERT_HTML) ,
            $hp->purify($params['item']->getTitle(), CODENDI_PURIFIER_CONVERT_HTML)
        ));
    }

    function _content($params, $view = null, $section = null)
    {
        $vSection = new Docman_View_ItemDetailsSectionPaste(
            $params['item'],
            $params['default_url'],
            $this->_controller,
            $params['itemToPaste'],
            $params['srcMode']
        );

        parent::_content($params, $vSection, 'actions');
    }
}
