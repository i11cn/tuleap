<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
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

namespace Tuleap\Tracker\Tests\REST\Workflows;

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use REST_TestDataBuilder;
use Tuleap\Tracker\Tests\REST\TrackerBase;

require_once __DIR__ . '/../bootstrap.php';

class TrackerWorkflowTransitionsTest extends TrackerBase
{

    /**
     * Returns all transitions combinations:
     * - current transitions (already used)
     * - available transitions to create (not used yet)
     */
    public function testGetAllTransitionCombinations() : array
    {
        $response = $this->getResponseByName(
            REST_TestDataBuilder::ADMIN_USER_NAME,
            $this->setup_client->get("trackers/$this->tracker_workflow_transitions_tracker_id")
        );

        $this->assertEquals($response->getStatusCode(), 200);

        $tracker = $response->json();

        $tracker_workflow_field_key = array_search(
            $tracker["workflow"]["field_id"],
            array_column(
                $tracker["fields"],
                "field_id"
            )
        );
        $all_field_values_ids = array_column($tracker["fields"][$tracker_workflow_field_key]["values"], "id");

        $all_transitions = [
            "transitions" => [],
            "missing_transitions" => []
        ];

        foreach ($tracker["workflow"]["transitions"] as $transition) {
            $all_transitions["transitions"][] = $transition;
        }

        foreach (array_merge([null], $all_field_values_ids) as $from_id) {
            foreach ($all_field_values_ids as $to_id) {
                $is_not_used_transition = empty(
                    array_filter($all_transitions["transitions"], function ($transition) use ($from_id, $to_id) {
                        return ($transition['from_id'] === $from_id && $transition['to_id'] === $to_id);
                    })
                );

                if ($from_id !== $to_id && $is_not_used_transition) {
                    $all_transitions["missing_transitions"][] = [
                        "from_id" => $from_id,
                        "to_id" => $to_id
                    ];
                }
            }
        };

        return $all_transitions;
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testPOSTTrackerWorkflowTransitionsSavesANewTransitionAndReturnsTheTransitionRepresentation($transition_combinations)
    {
        $available_transition = $transition_combinations["missing_transitions"][0];

        $params = json_encode(array(
            "tracker_id" => $this->tracker_workflow_transitions_tracker_id,
            "from_id" => $available_transition['from_id'] ?: 0,
            "to_id" => $available_transition['to_id']
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals($response->getStatusCode(), 201);

        $response_content = $response->json();

        $this->assertNotNull($response_content['id']);
        $this->assertEquals($response_content['uri'], "tracker_workflow_transitions/{$response_content['id']}");
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testPOSTTrackerWorkflowTransitionsRegularUsersHaveForbiddenAccess($transition_combinations)
    {
        $available_transition = $transition_combinations["missing_transitions"][0];

        $params = json_encode(array(
            "tracker_id" => $this->tracker_workflow_transitions_tracker_id,
            "from_id" => $available_transition['from_id'] ? $available_transition['from_id'] : 0,
            "to_id" => $available_transition['to_id']
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_2_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPOSTTrackerWorkflowTransitionsWhenTrackerDoesNotExistReturnsError()
    {
        $params = json_encode(array(
            "tracker_id" => 0,
            "from_id" => 0,
            "to_id" => 0
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPOSTTrackerWorkflowTransitionsWhenTrackerHasNoWorkflowReturnsError()
    {
        $params = json_encode(array(
            "tracker_id" => $this->tracker_workflows_tracker_id,
            "from_id" => 0,
            "to_id" => 0
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals($response->getStatusCode(), 404);
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testPOSTTrackerWorkflowTransitionsWhenTransitionAlreadyExistReturnsError($transition_combinations)
    {
        $used_transition = $transition_combinations["transitions"][0];

        $params = json_encode(array(
            "tracker_id" => $this->tracker_workflow_transitions_tracker_id,
            "from_id" => $used_transition['from_id'] ? $used_transition['from_id'] : 0,
            "to_id" => $used_transition['to_id']
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals($response->getStatusCode(), 400);
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testPOSTTrackerWorkflowTransitionsWhenFieldValueDoesNotExistReturnsError($transition_combinations)
    {
        $available_transition = $transition_combinations["missing_transitions"][0];

        $params = json_encode(array(
            "tracker_id" => $this->tracker_workflow_transitions_tracker_id,
            "from_id" => $available_transition['from_id'] ? $available_transition['from_id'] : 0,
            "to_id" => 1
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals($response->getStatusCode(), 404);
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testPOSTTrackerWorkflowTransitionsWhenFromIdEqualsToIdReturnsError($transition_combinations)
    {
        $available_transition = $transition_combinations["missing_transitions"][0];

        $params = json_encode(array(
            "tracker_id" => $this->tracker_workflow_transitions_tracker_id,
            "from_id" => $available_transition['to_id'],
            "to_id" => $available_transition['to_id']
        ));

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->post(
                'tracker_workflow_transitions',
                null,
                $params
            )
        );

        $this->assertEquals($response->getStatusCode(), 400);
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testDELETETrackerWorkflowTransitions($transition_combinations)
    {
        $used_transition = $transition_combinations["transitions"][1];

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->delete(
                'tracker_workflow_transitions/' . $used_transition['id'],
                null
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function testDELETETrackerWorkflowTransitionsReturns404WhenTransitionDoesNotExist()
    {
        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->delete(
                'tracker_workflow_transitions/0',
                null
            )
        );

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testGETTrackerWorkflowTransitionsReturnsTheTransitionRepresentation($transition_combinations)
    {
        $transition = $transition_combinations["transitions"][0];

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->get('tracker_workflow_transitions/' . $transition['id'])
        );

        $this->assertEquals($response->getStatusCode(), 200);

        $response_content = $response->json();
        $this->assertEquals($transition['id'], $response_content['id']);
        $this->assertEquals($transition['from_id'] ?: 0, $response_content['from_id']);
        $this->assertEquals($transition['to_id'], $response_content['to_id']);
    }

    /**
     * @depends testGetAllTransitionCombinations
     */
    public function testPATCHTrackerWorkflowTransitionsThrows400WhenNoAuthorizedUgroupsSelected($transition_combinations)
    {
        $transition = $transition_combinations["transitions"][0];

        $params = json_encode([
            "authorized_user_group_ids" => [],
            "not_empty_field_ids" => [],
            "is_comment_required" => true
        ]);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::ADMIN_USER_NAME,
            $this->client->patch(
                'tracker_workflow_transitions/' . $transition['id'],
                null,
                $params
            )
        );

        $this->assertEquals($response->getStatusCode(), 400);
    }

    public function testGetResolvedToClosedTransition(): int
    {
        $transition = $this->getSpecificTransition(
            $this->tracker_workflow_transitions_tracker_id,
            'status_id',
            'Resolved',
            'Closed'
        );
        return $transition['id'];
    }

    /**
     * @depends testGetResolvedToClosedTransition
     */
    public function testPATCHTrackerWorkflowTransitionsThenGETReturnsUpdatedTransition(int $transition_id)
    {
        $tracker_workflows_project_id = $this->getProjectId(self::TRACKER_WORKFLOWS_PROJECT_NAME);
        $a_user_group_id = $this->user_groups_ids[$tracker_workflows_project_id]['project_members'];

        $params = json_encode([
            "authorized_user_group_ids" => [$a_user_group_id],
            "not_empty_field_ids" => [],
            "is_comment_required" => true
        ]);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::ADMIN_USER_NAME,
            $this->client->patch(
                "tracker_workflow_transitions/$transition_id",
                null,
                $params
            )
        );
        $this->assertEquals($response->getStatusCode(), 200);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->get("tracker_workflow_transitions/$transition_id")
        );
        $this->assertEquals($response->getStatusCode(), 200);

        $response_content = $response->json();
        $this->assertEquals([$a_user_group_id], $response_content['authorized_user_group_ids']);
        $this->assertEquals([], $response_content['not_empty_field_ids']);
        $this->assertEquals(true, $response_content['is_comment_required']);
    }

    /**
     * @depends testGetResolvedToClosedTransition
     */
    public function testGETTrackerWorkflowTransitionActions(int $transition_id)
    {
        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->get("tracker_workflow_transitions/$transition_id/actions")
        );

        $this->assertEquals($response->getStatusCode(), 200);

        $post_actions = $response->json();

        $this->assertCount(4, $post_actions);

        $first_post_action = $post_actions[0];
        $this->assertSame("set_field_value", $first_post_action["type"]);
        $this->assertSame("date", $first_post_action["field_type"]);

        $second_post_action = $post_actions[1];
        $this->assertSame("set_field_value", $second_post_action["type"]);
        $this->assertSame("int", $second_post_action["field_type"]);

        $third_post_action = $post_actions[2];
        $this->assertSame("set_field_value", $third_post_action["type"]);
        $this->assertSame("float", $third_post_action["field_type"]);

        $forth_post_action = $post_actions[3];
        $this->assertSame("run_job", $forth_post_action["type"]);

        return $transition_id;
    }

    /**
     * @depends testGetResolvedToClosedTransition
     */
    public function testPUTTrackerWorkflowTransitionActions(int $transition_id)
    {
        $body = json_encode([
            "post_actions" => [
                [
                    "id" => null,
                    "type" => "run_job",
                    "job_url" => "http://example.test"
                ]
            ]
        ]);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->put(
                "tracker_workflow_transitions/$transition_id/actions",
                null,
                $body
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);

        return $transition_id;
    }

    /**
     * @depends testPUTTrackerWorkflowTransitionActions
     */
    public function testPUTTrackerWorkflowTransitionFrozenFieldsActionsNotPossible(int $transition_id)
    {
        $used_field_id = $this->getAUsedFieldId(
            $this->tracker_workflow_transitions_tracker_id,
            'status_id'
        );

        $body = json_encode([
            "post_actions" => [
                [
                    "id" => null,
                    "type" => "frozen_fields",
                    "field_ids" => [$used_field_id]
                ]
            ]
        ]);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->put(
                "tracker_workflow_transitions/$transition_id/actions",
                null,
                $body
            )
        );

        $this->assertEquals($response->getStatusCode(), 400);
    }

    /**
     * @depends testPUTTrackerWorkflowTransitionActions
     */
    public function testPUTTrackerWorkflowTransitionHiddenFieldsetsActionsNotPossible(int $transition_id)
    {
        $used_field_id = $this->getAUsedFieldId(
            $this->tracker_workflow_transitions_tracker_id,
            'fieldset1'
        );

        $body = json_encode([
            "post_actions" => [
                [
                    "id" => null,
                    "type" => "hidden_fieldsets",
                    "fieldset_ids" => [$used_field_id]
                ]
            ]
        ]);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->put(
                "tracker_workflow_transitions/$transition_id/actions",
                null,
                $body
            )
        );

        $this->assertEquals($response->getStatusCode(), 400);
    }

    /**
     * @depends testPUTTrackerWorkflowTransitionActions
     */
    public function testPUTTrackerWorkflowTransitionSetFieldValueFloatAction(int $transition_id)
    {
        $used_field_id = $this->getAUsedFieldId(
            $this->tracker_workflow_transitions_tracker_id,
            'il_flotte'
        );

        $json = json_encode([
            "post_actions" => [
                [
                    "type" => "set_field_value",
                    "field_type" => "float",
                    "field_id" => $used_field_id,
                    "value" => 1.2
                ]
            ]
        ]);

        $response = $this->getResponseByName(
            REST_TestDataBuilder::ADMIN_USER_NAME,
            $this->client->put(
                "tracker_workflow_transitions/$transition_id/actions",
                null,
                $json
            )
        );

        $this->assertEquals(200, $response->getStatusCode());

        $response_get = $this->getResponseByName(
            REST_TestDataBuilder::TEST_USER_1_NAME,
            $this->client->get("tracker_workflow_transitions/$transition_id/actions")
        );

        $this->assertEquals(200, $response_get->getStatusCode());
        $response_get_content = $response_get->json();
        $this->assertCount(1, $response_get_content);

        $this->assertSame("set_field_value", $response_get_content[0]["type"]);
        $this->assertSame("float", $response_get_content[0]["field_type"]);
        $this->assertSame(1.2, $response_get_content[0]["value"]);
        $this->assertSame($used_field_id, $response_get_content[0]["field_id"]);
    }
}
