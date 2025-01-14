import _ from "lodash";
import angular from "angular";
import "angular-mocks";

import drop_module from "./drop.js";

describe("DroppedService -", function() {
    var $q,
        DroppedService,
        ProjectService,
        MilestoneService,
        BacklogItemService,
        RestErrorService,
        rest_error;

    beforeEach(function() {
        angular.mock.module(drop_module);

        angular.mock.inject(function(
            _$q_,
            _DroppedService_,
            _ProjectService_,
            _MilestoneService_,
            _BacklogItemService_,
            _RestErrorService_
        ) {
            $q = _$q_;
            DroppedService = _DroppedService_;
            ProjectService = _ProjectService_;
            MilestoneService = _MilestoneService_;
            BacklogItemService = _BacklogItemService_;
            RestErrorService = _RestErrorService_;

            var returnPromise = function(method) {
                var self = this;
                spyOn(self, method).and.returnValue($q.defer().promise);
            };

            ProjectService = _ProjectService_;
            _(["reorderBacklog", "removeAddReorderToBacklog", "removeAddToBacklog"]).forEach(
                returnPromise,
                ProjectService
            );

            MilestoneService = _MilestoneService_;
            _([
                "reorderBacklog",
                "reorderContent",
                "addReorderToContent",
                "addToContent",
                "removeAddReorderToBacklog",
                "removeAddToBacklog",
                "removeAddReorderToContent",
                "removeAddToContent"
            ]).forEach(returnPromise, MilestoneService);

            BacklogItemService = _BacklogItemService_;
            _([
                "reorderBacklogItemChildren",
                "removeAddReorderBacklogItemChildren",
                "removeAddBacklogItemChildren"
            ]).forEach(returnPromise, BacklogItemService);

            spyOn(RestErrorService, "setError");
        });

        installPromiseMatchers();

        rest_error = {
            data: {
                error: {
                    code: 404,
                    message: "Not Found"
                }
            }
        };
    });

    describe("defineComparedTo() - ", function() {
        describe("when only one element is dragged, ", function() {
            var dragged_item = { id: 4 };
            var dropped_items = [{ id: 4 }];

            it("should return before the first item", function() {
                var item_list = [{ id: 4 }, { id: 1 }, { id: 2 }, { id: 3 }, { id: 5 }, { id: 6 }];

                expect(
                    DroppedService.defineComparedTo(item_list, dragged_item, dropped_items)
                ).toEqual({ direction: "before", item_id: 1 });
            });

            it("should return after the first item", function() {
                var item_list = [{ id: 1 }, { id: 4 }, { id: 2 }, { id: 3 }, { id: 5 }, { id: 6 }];

                expect(
                    DroppedService.defineComparedTo(item_list, dragged_item, dropped_items)
                ).toEqual({ direction: "after", item_id: 1 });
            });

            it("should return after the last item", function() {
                var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 5 }, { id: 6 }, { id: 4 }];

                expect(
                    DroppedService.defineComparedTo(item_list, dragged_item, dropped_items)
                ).toEqual({ direction: "after", item_id: 6 });
            });
        });

        describe("when multiple elements are dragged, ", function() {
            var dragged_item = { id: 5 };
            var dropped_items = [{ id: 2 }, { id: 5 }];

            it("should return before the first item", function() {
                var item_list = [{ id: 5 }, { id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 6 }];

                expect(
                    DroppedService.defineComparedTo(item_list, dragged_item, dropped_items)
                ).toEqual({ direction: "before", item_id: 1 });
            });

            it("should return after the first item", function() {
                var item_list = [{ id: 1 }, { id: 5 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 6 }];

                expect(
                    DroppedService.defineComparedTo(item_list, dragged_item, dropped_items)
                ).toEqual({ direction: "after", item_id: 1 });
            });

            it("should return after the last item", function() {
                var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 6 }, { id: 5 }];

                expect(
                    DroppedService.defineComparedTo(item_list, dragged_item, dropped_items)
                ).toEqual({ direction: "after", item_id: 6 });
            });
        });
    });

    describe("defineComparedToBeFirstItem() - ", function() {
        it("should return before the first item", function() {
            var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];
            var moved_items = [{ id: 2 }, { id: 5 }];

            expect(DroppedService.defineComparedToBeFirstItem(item_list, moved_items)).toEqual({
                direction: "before",
                item_id: 1
            });
        });

        it("should return before the first item not in selection", function() {
            var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];
            var moved_items = [{ id: 1 }, { id: 5 }];

            expect(DroppedService.defineComparedToBeFirstItem(item_list, moved_items)).toEqual({
                direction: "before",
                item_id: 2
            });
        });

        it("should return null if all items are selected", function() {
            var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];
            var moved_items = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];

            expect(DroppedService.defineComparedToBeFirstItem(item_list, moved_items)).toEqual(
                null
            );
        });
    });

    describe("defineComparedToBeLastItem() - ", function() {
        it("should return after the last item", function() {
            var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];
            var moved_items = [{ id: 2 }, { id: 5 }];

            expect(DroppedService.defineComparedToBeLastItem(item_list, moved_items)).toEqual({
                direction: "after",
                item_id: 6
            });
        });

        it("should return after the last item not in selection", function() {
            var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];
            var moved_items = [{ id: 4 }, { id: 6 }];

            expect(DroppedService.defineComparedToBeLastItem(item_list, moved_items)).toEqual({
                direction: "after",
                item_id: 5
            });
        });

        it("should return null if all items are selected", function() {
            var item_list = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];
            var moved_items = [{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }, { id: 6 }];

            expect(DroppedService.defineComparedToBeLastItem(item_list, moved_items)).toEqual(null);
        });
    });

    describe("reorderBacklog() -", function() {
        it("should call the REST route that reorder project backlog", function() {
            DroppedService.reorderBacklog(
                1,
                {},
                {
                    rest_base_route: "projects",
                    rest_route_id: 2
                }
            );
            expect(ProjectService.reorderBacklog).toHaveBeenCalledWith(2, 1, {});
        });

        it("should call the REST route that reorder milestone backlog", function() {
            DroppedService.reorderBacklog(
                1,
                {},
                { rest_base_route: "milestones", rest_route_id: 2 }
            );
            expect(MilestoneService.reorderBacklog).toHaveBeenCalledWith(2, 1, {});
        });

        it("Given that the server was unreachable, when I reorder the backlog, then an error will be displayed", function() {
            var reorder_request = $q.defer();
            ProjectService.reorderBacklog.and.returnValue(reorder_request.promise);

            var promise = DroppedService.reorderBacklog(
                1,
                {},
                {
                    rest_base_route: "projects",
                    rest_route_id: 2
                }
            );
            reorder_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });

    describe("reorderSubmilestone() -", function() {
        it("should call the REST route that reorder milestone content", function() {
            DroppedService.reorderSubmilestone(1, {}, 2);
            expect(MilestoneService.reorderContent).toHaveBeenCalledWith(2, 1, {});
        });

        it("Given that the server was unreachable, when I reorder a submilestone, then an error will be displayed", function() {
            var reorder_request = $q.defer();
            MilestoneService.reorderContent.and.returnValue(reorder_request.promise);

            var promise = DroppedService.reorderSubmilestone(1, {}, 2);
            reorder_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });

    describe("reorderBacklogItemChildren() -", function() {
        it("should call the REST route that reorder milestone content", function() {
            DroppedService.reorderBacklogItemChildren(1, {}, 2);
            expect(BacklogItemService.reorderBacklogItemChildren).toHaveBeenCalledWith(2, 1, {});
        });

        it("Given that the server was unreachable, when I reorder the children of a backlog item, then an error will be displayed", function() {
            var reorder_request = $q.defer();
            BacklogItemService.reorderBacklogItemChildren.and.returnValue(reorder_request.promise);

            var promise = DroppedService.reorderBacklogItemChildren(1, {}, 2);
            reorder_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });

    describe("moveFromBacklogToSubmilestone:", function() {
        it("should call the REST route that add an item in milestone and reorder its content", function() {
            DroppedService.moveFromBacklogToSubmilestone(1, {}, 2);
            expect(MilestoneService.addReorderToContent).toHaveBeenCalledWith(2, 1, {});
        });

        it("should call the REST route that add an item in milestone without reorder it", function() {
            DroppedService.moveFromBacklogToSubmilestone(1, undefined, 2);
            expect(MilestoneService.addToContent).toHaveBeenCalledWith(2, 1);
        });

        it("Given that the server was unreachable, when I move an item from the backlog to a submilestone, then an error will be displayed", function() {
            var move_request = $q.defer();
            MilestoneService.addReorderToContent.and.returnValue(move_request.promise);

            var promise = DroppedService.moveFromBacklogToSubmilestone(1, {}, 2);
            move_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });

    describe("moveFromChildrenToChildren() -", function() {
        it("should call the REST route that remove a child from a BI, add it to another BI and reorder the new parent BI", function() {
            DroppedService.moveFromChildrenToChildren(1, {}, 2, 3);
            expect(BacklogItemService.removeAddReorderBacklogItemChildren).toHaveBeenCalledWith(
                2,
                3,
                1,
                {}
            );
        });

        it("should call the REST route that remove a child from a BI, add it to another empty BI", function() {
            DroppedService.moveFromChildrenToChildren(1, undefined, 2, 3);
            expect(BacklogItemService.removeAddBacklogItemChildren).toHaveBeenCalledWith(2, 3, 1);
        });

        it("Given that the server was unreachable, when I move a child from an item to another item, then an error will be displayed", function() {
            var move_request = $q.defer();
            BacklogItemService.removeAddReorderBacklogItemChildren.and.returnValue(
                move_request.promise
            );

            var promise = DroppedService.moveFromChildrenToChildren(1, {}, 2, 3);
            move_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });

    describe("moveFromSubmilestoneToBacklog() -", function() {
        it("should call the REST route that remove a BI from a milestone and add it to the project backlog and reorder it", function() {
            DroppedService.moveFromSubmilestoneToBacklog(1, {}, 2, {
                rest_base_route: "projects",
                rest_route_id: 3
            });
            expect(ProjectService.removeAddReorderToBacklog).toHaveBeenCalledWith(2, 3, 1, {});
        });

        it("should call the REST route that remove a BI from a milestone and add it to the project backlog", function() {
            DroppedService.moveFromSubmilestoneToBacklog(1, undefined, 2, {
                rest_base_route: "projects",
                rest_route_id: 3
            });
            expect(ProjectService.removeAddToBacklog).toHaveBeenCalledWith(2, 3, 1);
        });

        it("should call the REST route that remove a BI from a milestone and add it to the project backlog and reorder it", function() {
            DroppedService.moveFromSubmilestoneToBacklog(1, {}, 2, {
                rest_base_route: "milestones",
                rest_route_id: 3
            });
            expect(MilestoneService.removeAddReorderToBacklog).toHaveBeenCalledWith(2, 3, 1, {});
        });

        it("should call the REST route that remove a BI from a milestone and add it to the project backlog", function() {
            DroppedService.moveFromSubmilestoneToBacklog(1, undefined, 2, {
                rest_base_route: "milestones",
                rest_route_id: 3
            });
            expect(MilestoneService.removeAddToBacklog).toHaveBeenCalledWith(2, 3, 1);
        });

        it("Given that the server was unreachable, when I move an item from a submilestone to the backlog, then an error will be displayed", function() {
            var move_request = $q.defer();
            ProjectService.removeAddReorderToBacklog.and.returnValue(move_request.promise);

            var promise = DroppedService.moveFromSubmilestoneToBacklog(1, {}, 2, {
                rest_base_route: "projects",
                rest_route_id: 3
            });
            move_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });

    describe("moveFromSubmilestoneToSubmilestone() -", function() {
        it("should call the REST route that remove a BI from a milestone and add it to another milestone backlog and reorder it", function() {
            DroppedService.moveFromSubmilestoneToSubmilestone(1, {}, 2, 3);
            expect(MilestoneService.removeAddReorderToContent).toHaveBeenCalledWith(2, 3, 1, {});
        });

        it("should call the REST route that remove a BI from a milestone and add it to another milestone backlog", function() {
            DroppedService.moveFromSubmilestoneToSubmilestone(1, undefined, 2, 3);
            expect(MilestoneService.removeAddToContent).toHaveBeenCalledWith(2, 3, 1);
        });

        it("Given that the server was unreachable, when I move an item from a submilestone to another submilestone, then an error will be displayed", function() {
            var move_request = $q.defer();
            MilestoneService.removeAddReorderToContent.and.returnValue(move_request.promise);

            var promise = DroppedService.moveFromSubmilestoneToSubmilestone(1, {}, 2, 3);
            move_request.reject(rest_error);

            expect(promise).toBeRejected();
            expect(RestErrorService.setError).toHaveBeenCalledWith(rest_error.data.error);
        });
    });
});
