/*
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

import { shallowMount } from "@vue/test-utils";
import localVue from "../../../helpers/local-vue.js";
import { createStoreMock } from "@tuleap-vue-components/store-wrapper.js";
import QuickLookButton from "./QuickLookButton.vue";
import { TYPE_FOLDER } from "../../../constants.js";
import {
    rewire as rewireEventBus,
    restore as restoreEventBus
} from "../../../helpers/event-bus.js";

describe("QuickLookButton", () => {
    let factory, store, event_bus;
    beforeEach(() => {
        store = createStoreMock({});
        store.getters.is_item_a_folder = () => true;
        factory = (props = {}) => {
            return shallowMount(QuickLookButton, {
                localVue,
                propsData: { ...props },
                mocks: { $store: store }
            });
        };
        event_bus = jasmine.createSpyObj("event_bus", ["$emit"]);
        rewireEventBus(event_bus);
    });

    afterEach(() => {
        restoreEventBus();
    });

    it(`Emit displayQuickLook event with correct parameters when user click on button`, () => {
        const item = { type: TYPE_FOLDER, user_can_write: true };
        const wrapper = factory({ item });

        wrapper.find("[data-test=document-quick-look-button]").trigger("click");
        expect(event_bus.$emit).toHaveBeenCalledWith("toggle-quick-look", {
            details: { item }
        });
    });
});
