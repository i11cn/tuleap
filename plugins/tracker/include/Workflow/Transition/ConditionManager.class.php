<?php
/**
 * Copyright (c) Enalean, 2011 - 2016. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use Tuleap\Tracker\Workflow\Transition\Condition\CannotCreateTransitionException;

/**
 * Manager for Condition
 */

class Transition_ConditionManager {

    /**
     * Process the artifact functions
     *
     * @param Transition      $transition   The transition
     * @param Codendi_Request $request      The data from the user
     * @param PFUser            $current_user The current user
     *
     * @return void
     */
    public function process(Transition $transition, Codendi_Request $request, PFUser $current_user)
    {
        $transition_condition_factory = $this->getConditionFactory();

        $list_fields_id       = $request->get('add_notempty_condition');
        $is_comment_required = $request->get('is_comment_required');

        try {
            // Create new condition
            if (is_array($list_fields_id)) {
                $transition_condition_factory->addCondition($transition, $list_fields_id, $is_comment_required);
            }
        } catch (CannotCreateTransitionException $excpetion) {
            throw $excpetion;
        }

        // Loop over defined actions and update them if relevant
        foreach ($transition->getConditions() as $condition) {
            $condition->process($request);
        }
    }

    /**
     * Wrapper for Transition_PostActionFactory
     *
     * @return Transition_PostActionFactory
     */
    public function getConditionFactory()
    {
        return Workflow_Transition_ConditionFactory::build();
    }
}
?>