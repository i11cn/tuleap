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
namespace Tuleap\AgileDashboard\REST\v1\Kanban;

use Luracast\Restler\RestException;

class KanbanAddRepresentation {
    /**
     * @var {@type array}
     */
    public $ids;

    /**
     * @throws RestException
     */
    public function checkFormat()
    {
        $this->isArrayOfInt('ids');
        if (count($this->ids) == 0) {
            throw new RestException(400, "invalid value specified for `ids`. Expected: array of integers");
        }
    }

    private function isArrayOfInt($name)
    {
        if (! is_array($this->$name)) {
            throw new RestException(400, "invalid value specified for `$name`. Expected: array of integers");
        }
        foreach ($this->$name as $id) {
            if (! is_int($id)) {
                throw new RestException(400, "invalid value specified for `$name`. Expected: array of integers");
            }
        }
    }
}
