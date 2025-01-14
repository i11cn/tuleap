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

const path = require("path");
const webpack_config = require("./webpack.vue.js");
const karma_configurator = require("../../../tools/utils/scripts/karma-configurator.js");

webpack_config.mode = "development";

module.exports = function(config) {
    const coverage_dir = path.resolve(__dirname, "./coverage");
    const coverage_folder_name = "vue_apps";
    const base_config = karma_configurator.setupBaseKarmaConfig(
        config,
        webpack_config,
        coverage_dir,
        coverage_folder_name
    );

    Object.assign(base_config, {
        files: ["project/admin/services/app.spec.js"],
        preprocessors: {
            "project/admin/services/app.spec.js": ["webpack"]
        }
    });

    config.set(base_config);
};
