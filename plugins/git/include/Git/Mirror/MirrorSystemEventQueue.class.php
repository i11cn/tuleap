<?php
/**
 * Copyright (c) Enalean, 2015. All Rights Reserved.
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

class Git_Mirror_MirrorSystemEventQueue extends SystemEventQueue {

    /**
     * @var Logger
     */
    private $logger;

    public const NAME = 'grokmirror';

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getName()
    {
        return self::NAME;
    }

    public function getLabel()
    {
        return dgettext('tuleap-git', 'Grok Mirror');
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
