<?php
/**
 * Copyright (c) Enalean, 2015. All Rights Reserved.
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
namespace User\XML\Import;

use TuleapTestCase;

class ToBeMappedUser_isActionAllowedTest extends TuleapTestCase {

    /** @var ToBeMappedUser */
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = new ToBeMappedUser(
            'to.be.mapped',
            'To Be Mapped',
            array(
                aUser()->withUserName('cstevens')->build()
            ),
            104,
            'cs1234'
        );
    }

    public function itReturnsFalseWhenActionIsCreate()
    {
        $this->assertFalse($this->user->isActionAllowed('create'));
    }

    public function itReturnsFalseWhenActionIsActivate()
    {
        $this->assertFalse($this->user->isActionAllowed('activate'));
    }

    public function itReturnsFalseWhenActionIsMap()
    {
        $this->assertTrue($this->user->isActionAllowed('map'));
    }
}
