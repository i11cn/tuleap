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
 *
 */

import { Vue } from "vue/types/vue";
import VueDOMPurifyHTML from "vue-dompurify-html";
import GetTextPlugin from "vue-gettext";
import { createLocalVue } from "@vue/test-utils";

export function createTaskboardLocalVue(): typeof Vue {
    const local_vue = createLocalVue();
    local_vue.use(GetTextPlugin, {
        translations: {},
        silent: true
    });
    local_vue.use(VueDOMPurifyHTML);

    return local_vue;
}
