/*
 * Copyright (c) Enalean, 2016-Present. All Rights Reserved.
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

import angular from "angular";
import tuleap_pullrequest_module from "tuleap-pullrequest-module";

import "angular-mocks";

describe("PullRequestRestService -", function() {
    var $httpBackend, PullRequestRestService, ErrorModalService;

    beforeEach(function() {
        angular.mock.module(tuleap_pullrequest_module);

        angular.mock.inject(function(
            _$httpBackend_,
            _ErrorModalService_,
            _PullRequestRestService_
        ) {
            $httpBackend = _$httpBackend_;
            ErrorModalService = _ErrorModalService_;
            PullRequestRestService = _PullRequestRestService_;
        });

        spyOn(ErrorModalService, "showError");

        installPromiseMatchers();
    });

    afterEach(function() {
        $httpBackend.verifyNoOutstandingExpectation();
        $httpBackend.verifyNoOutstandingRequest();
    });

    describe("getPullRequest()", function() {
        it("Given a pull_request id, when I get it, then a GET request will be sent to Tuleap and a promise will be resolved with a pull_request object", function() {
            var pull_request_id = 83;

            var pull_request = {
                id: pull_request_id,
                title: "Asking a PR",
                user_id: 101,
                branch_src: "sample-pr",
                branch_dest: "master",
                repository: {
                    id: 1
                },
                repository_dest: {
                    id: 2
                },
                status: "review",
                creation_date: "2016-04-19T09:20:21+00:00"
            };

            $httpBackend
                .expectGET("/api/v1/pull_requests/" + pull_request_id)
                .respond(angular.toJson(pull_request));

            var promise = PullRequestRestService.getPullRequest(pull_request_id);
            $httpBackend.flush();

            expect(promise).toBeResolvedWith(pull_request);
        });

        it("when the server responds with an error, then the error modal will be shown", function() {
            var pull_request_id = 48;

            $httpBackend
                .expectGET("/api/v1/pull_requests/" + pull_request_id)
                .respond(403, "Forbidden");

            var promise = PullRequestRestService.getPullRequest(pull_request_id);

            expect(promise).toBeRejected();
            expect(ErrorModalService.showError).toHaveBeenCalledWith(
                jasmine.objectContaining({
                    status: 403,
                    statusText: ""
                })
            );
        });
    });
});
