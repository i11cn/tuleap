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

import { expiringLocalStorage } from "./storage.js";

describe("Persistent storage with an expiration", () => {
    beforeEach(function() {
        jasmine.clock().install();
        jasmine.clock().mockDate();
    });

    afterEach(function() {
        jasmine.clock().uninstall();
        localStorage.clear();
    });

    it("When the key has not expired the value can be retrieved", () => {
        const storage = expiringLocalStorage(3600);

        storage.setItem("mykey", "value");
        jasmine.clock().tick(10 * 1000);
        expect(storage.getItem("mykey")).toBe("value");
    });

    it("When the key has expired the value can not be retrieved", () => {
        const storage = expiringLocalStorage(3600);

        storage.setItem("mykey", "value");
        jasmine.clock().tick(3601 * 1000);
        expect(storage.getItem("mykey")).toBe(null);
    });
});
