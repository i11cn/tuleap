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
import UpdateProperties from "./UpdateProperties.vue";
import {
    rewire as rewireEventBus,
    restore as restoreEventBus
} from "../../../helpers/event-bus.js";

describe("UpdateProperties", () => {
    let document_action_button_factory, event_bus;
    beforeEach(() => {
        document_action_button_factory = (props = {}) => {
            return shallowMount(UpdateProperties, {
                localVue,
                propsData: { ...props }
            });
        };

        event_bus = jasmine.createSpyObj("event_bus", ["$emit"]);
        rewireEventBus(event_bus);
    });

    afterEach(() => {
        restoreEventBus();
    });

    it(`Click on folder open the corresponding modal`, () => {
        const item = {
            user_can_write: true
        };
        const wrapper = document_action_button_factory({ item });
        wrapper.find("[data-test=document-dropdown-update-properties]").trigger("click");

        expect(event_bus.$emit).toHaveBeenCalledWith("show-update-item-metadata-modal", {
            detail: { current_item: item }
        });
    });
});
