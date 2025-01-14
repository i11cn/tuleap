<?php
/**
 * Copyright (c) Enalean, 2016-Present. All rights reserved
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
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

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class HudsonBuild {

    protected $hudson_build_url;

    /**
     * @var SimpleXMLElement
     */
    protected $dom_build;
    /**
     * @var ClientInterface
     */
    private $http_client;
    /**
     * @var RequestFactoryInterface
     */
    private $request_factory;

    /**
     * Construct an Hudson build from a build URL
     */
    public function __construct(
        string $hudson_build_url,
        ClientInterface $http_client,
        RequestFactoryInterface $request_factory
    ) {
        $parsed_url = parse_url($hudson_build_url);

        if ( ! $parsed_url || ! array_key_exists('scheme', $parsed_url) ) {
            throw new HudsonJobURLMalformedException($GLOBALS['Language']->getText('plugin_hudson','wrong_job_url', array($hudson_build_url)));
        }

        $this->hudson_build_url = $hudson_build_url . "/api/xml";
        $this->http_client      = $http_client;
        $this->request_factory  = $request_factory;

        $this->dom_build = $this->_getXMLObject($this->hudson_build_url);
    }

    protected function _getXMLObject(string $hudson_build_url)
    {
        $response = $this->http_client->sendRequest(
            $this->request_factory->createRequest('GET', $hudson_build_url)
        );
        if ($response->getStatusCode() !== 200) {
            throw new HudsonJobURLFileNotFoundException($GLOBALS['Language']->getText('plugin_hudson','job_url_file_not_found', array($hudson_build_url)));
        }

        $xmlobj = simplexml_load_string($response->getBody()->getContents());
        if ($xmlobj !== false) {
            return $xmlobj;
        }
        throw new HudsonJobURLFileException($GLOBALS['Language']->getText('plugin_hudson','job_url_file_error', array($hudson_build_url)));
    }

    function getDom()
    {
        return $this->dom_build;
    }

    function getBuildStyle()
    {
        return $this->dom_build->getName();
    }

    function isBuilding()
    {
        return ($this->dom_build->building == "true");
    }

    function getUrl()
    {
        return (string) $this->dom_build->url;
    }

    function getResult()
    {
        return (string) $this->dom_build->result;
    }

    function getNumber()
    {
        return (int) $this->dom_build->number;
    }

    function getDuration()
    {
        return (int) $this->dom_build->duration;
    }

    function getTimestamp()
    {
        return (int) $this->dom_build->timestamp;
    }

    function getBuildTime()
    {
        return format_date($GLOBALS['Language']->getText('system', 'datefmt'), substr($this->getTimestamp(), 0, -3));
    }

    function getStatusIcon()
    {
        $color = 'red';
        if ($this->getResult() == 'SUCCESS') {
            $color = 'blue';
        } else if ($this->getResult() == 'UNSTABLE') {
            $color = 'yellow';
        }
        return hudsonPlugin::ICONS_PATH .'status_'. $color .'.png';
    }
}
