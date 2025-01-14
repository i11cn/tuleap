<?php
/**
 * Copyright (c) Enalean, 2013-2014. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

namespace Tuleap\AgileDashboard\REST\v1;

use Tuleap\Tracker\REST\TrackerReference;
use Tuleap\REST\ResourceReference;
use Tuleap\REST\JsonCast;
use Tuleap\REST\v1\BacklogItemParentReferenceBase;
use Tracker_Artifact;

class BacklogItemParentReference extends BacklogItemParentReferenceBase {

    public function build(Tracker_Artifact $backlog_item)
    {
        $this->id    = JsonCast::toInt($backlog_item->getId());
        $this->label = $backlog_item->getTitle();
        $this->uri   = ResourceReference::NO_ROUTE;

        $this->tracker = new TrackerReference();
        $this->tracker->build($backlog_item->getTracker());
    }
}