/*
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

import { disableFormSubmit, enableFormSubmit, findAllHiddenInputByNames } from "./form-adapter.js";
import {
    rewire$isThereAFileCurrentlyUploading,
    rewire$increaseCurrentlyUploadingFilesNumber,
    rewire$decreaseCurrentlyUploadingFilesNumber,
    restore as restoreState
} from "./forms-being-uploaded-state.js";

describe(`form-adapter`, () => {
    let isThereAFileCurrentlyUploading,
        increaseCurrentlyUploadingFilesNumber,
        decreaseCurrentlyUploadingFilesNumber;
    beforeEach(() => {
        isThereAFileCurrentlyUploading = jasmine.createSpy("isThereAFileCurrentlyUploading");
        increaseCurrentlyUploadingFilesNumber = jasmine.createSpy(
            "increaseCurrentlyUploadingFilesNumber"
        );
        decreaseCurrentlyUploadingFilesNumber = jasmine.createSpy(
            "decreaseCurrentlyUploadingFilesNumber"
        );
        rewire$isThereAFileCurrentlyUploading(isThereAFileCurrentlyUploading);
        rewire$increaseCurrentlyUploadingFilesNumber(increaseCurrentlyUploadingFilesNumber);
        rewire$decreaseCurrentlyUploadingFilesNumber(decreaseCurrentlyUploadingFilesNumber);
    });

    afterEach(() => {
        restoreState();
    });

    describe(`findAllHiddenInputByNames()`, () => {
        let form;
        beforeEach(() => {
            form = jasmine.createSpyObj("form", ["querySelectorAll"]);
        });

        it(`finds all hidden inputs that have one of the given
        field_names as [name] attribute`, () => {
            const first_field_name = "sonnikins";
            const second_field_name = "ebon";

            findAllHiddenInputByNames(form, [first_field_name, second_field_name]);

            expect(form.querySelectorAll).toHaveBeenCalledWith(
                `input[type=hidden][name=sonnikins],input[type=hidden][name=ebon]`
            );
        });

        it(`finds one hidden input when only one field_name is given`, () => {
            const field_name = "Phlebodium";

            findAllHiddenInputByNames(form, [field_name]);

            expect(form.querySelectorAll).toHaveBeenCalledWith(
                `input[type=hidden][name=Phlebodium]`
            );
        });
    });

    describe(`disableFormSubmit()`, () => {
        let form;
        beforeEach(() => {
            form = jasmine.createSpyObj("form", ["querySelectorAll", "addEventListener"]);
            form.querySelectorAll.and.returnValue([]);
        });

        it(`disables all the form's submit buttons`, () => {
            const first_button = {};
            const second_button = {};
            form.querySelectorAll.and.returnValue([first_button, second_button]);

            disableFormSubmit(form);

            expect(first_button.disabled).toBe(true);
            expect(second_button.disabled).toBe(true);
        });

        it(`prevents the submit event on the form`, () => {
            let triggerSubmit;
            form.addEventListener.and.callFake((event_name, handler) => {
                triggerSubmit = handler;
            });

            disableFormSubmit(form);
            const submit_event = jasmine.createSpyObj("event", [
                "preventDefault",
                "stopPropagation"
            ]);
            triggerSubmit(submit_event);

            expect(form.addEventListener).toHaveBeenCalledWith("submit", jasmine.any(Function));
            expect(submit_event.preventDefault).toHaveBeenCalled();
            expect(submit_event.stopPropagation).toHaveBeenCalled();
        });

        it(`keeps in memory that a file is uploading`, () => {
            disableFormSubmit(form);

            expect(increaseCurrentlyUploadingFilesNumber).toHaveBeenCalled();
        });
    });

    describe(`enableFormSubmit()`, () => {
        let form;
        beforeEach(() => {
            form = jasmine.createSpyObj("form", ["querySelectorAll", "removeEventListener"]);
            form.querySelectorAll.and.returnValue([]);
        });

        it(`sets in state that the file is no longer uploading`, () => {
            enableFormSubmit(form);

            expect(decreaseCurrentlyUploadingFilesNumber).toHaveBeenCalled();
        });

        it(`when there were 2 files being uploaded, it does nothing`, () => {
            isThereAFileCurrentlyUploading.and.returnValue(true);

            enableFormSubmit(form);

            expect(form.removeEventListener).not.toHaveBeenCalled();
        });

        describe(`when the last file being uploaded is finished or in error`, () => {
            beforeEach(() => {
                isThereAFileCurrentlyUploading.and.returnValue(false);
            });

            it(`enables back all the form's submit buttons`, () => {
                const first_button = { disabled: true };
                const second_button = { disabled: true };
                form.querySelectorAll.and.returnValue([first_button, second_button]);

                enableFormSubmit(form);

                expect(first_button.disabled).toBe(false);
                expect(second_button.disabled).toBe(false);
            });

            it(`stops blocking the submit event on the form`, () => {
                enableFormSubmit(form);

                expect(form.removeEventListener).toHaveBeenCalledWith(
                    "submit",
                    jasmine.any(Function)
                );
            });
        });
    });
});
