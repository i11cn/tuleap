/*
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

import { shallowMount } from "@vue/test-utils";
import localVue from "../../../helpers/local-vue";
import CreateNewVersionEmptyModal from "./CreateNewVersionEmptyModal.vue";
import { TYPE_EMPTY, TYPE_FILE, TYPE_LINK } from "../../../constants.js";
import { tlp } from "tlp-mocks";
import { restore as restoreAction } from "../../../store/actions.js";
import { createStoreMock } from "@tuleap-vue-components/store-wrapper.js";

describe("CreateNewVersionEmptyModal", () => {
    let factory, store;

    beforeEach(() => {
        store = createStoreMock({}, { project_ugroups: null, error: {} });

        factory = props => {
            return shallowMount(CreateNewVersionEmptyModal, {
                localVue,
                propsData: { ...props },
                mocks: { $store: store }
            });
        };

        tlp.modal.and.returnValue({
            addEventListener: () => {},
            show: () => {},
            hide: () => {}
        });
    });

    afterEach(() => {
        restoreAction();
    });

    it("Default type for creation of new link version of an empty document is file", () => {
        const wrapper = factory({
            item: { id: 10, type: TYPE_EMPTY }
        });

        expect(wrapper.vm.new_item_version.type).toBe(TYPE_FILE);
    });
    it("It should create a new link version from an empty document", () => {
        const wrapper = factory({
            item: { id: 10, type: TYPE_EMPTY }
        });
        wrapper.setData({
            new_item_version: {
                type: TYPE_LINK
            }
        });
        store.dispatch.and.callFake(actionMethodName => {
            if (actionMethodName === "createNewVersionFromEmpty") {
                expect(wrapper.vm.is_loading).toBe(true);
                expect(wrapper.vm.new_item_version.type).toBe(TYPE_LINK);
                store.state.error.has_modal_error = false;
            }
        });
        wrapper.find("form").trigger("submit.prevent");
    });
});
