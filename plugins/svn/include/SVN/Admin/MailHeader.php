<?php
/**
  * Copyright (c) Enalean, 2016 - 2018. All rights reserved
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
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with Tuleap. If not, see <http://www.gnu.org/licenses/
  */

namespace Tuleap\SVN\Admin;

use Project;
use Tuleap\SVN\Repository\Repository;

class MailHeader
{
    private $header;
    private $repository;

    public function __construct(Repository $repository, $header)
    {
        $this->repository = $repository;
        $this->header     = $header;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}