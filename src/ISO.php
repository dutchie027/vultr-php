<?php

/**
 * PHP Wrapper to Interact with Vultr 2.0 API
 *
 * @package Vultr
 * @version 2.0
 * @author  https://github.com/dutchie027
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @see     https://github.com/dutche027/vultr-php
 * @see     https://packagist.org/packages/dutchie027/vultr
 * @see     https://www.vultr.com/api/v2
 *
 */

namespace dutchie027\Vultr;

class ISO
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * __construct
     * Takes reference from \API
     *
     * @param object $api API
     *
     * @return object
     *
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadISOs();
    }

    /**
     * Array of All Plan IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Array of ISO Information
     *
     * @var array
     */
    public $isos = [];

    /**
     * Count of Total ISOs
     *
     * @var int
     */
    protected $total_isos;

    /**
     * listPublicISOs
     * Lists Public ISOs
     *
     *
     * @return string
     *
     */
    public function listPublicISOs()
    {
        return $this->api->makeAPICall('GET', $this->api::ISO_PUBLIC_URL);
    }

    /**
     * listISOs
     * Lists ISO Files
     *
     *
     * @return string
     *
     */
    public function listISOs()
    {
        return $this->api->makeAPICall('GET', $this->api::ISO_URL);
    }

    /**
     * deleteISO
     * Lists ISO Files
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function deleteISO($id)
    {
        return $this->api->makeAPICall('DELETE', $this->api::ISO_URL . "/" . $id);
    }

    /**
     * getISO
     * Get ISO Information
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function getISO($id)
    {
        return $this->api->makeAPICall('GET', $this->api::ISO_URL . "/" . $id);
    }

    /**
     * createISO
     * Create ISO
     *
     * @var string $url
     *
     * @return string
     *
     */
    public function createISO($url)
    {
        $ba['url'] = $url;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::ISO_URL, $body);
    }

    /**
     * listIds
     * Prints Instance IDs to stdout
     *
     *
     * @return void
     *
     */
    public function listIds()
    {
        foreach ($this->ids as $id) {
            print $id . PHP_EOL;
        }
    }

    /**
     * loadISOs
     * Loads ISO Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadISOs()
    {
        $ia = json_decode($this->listISOs(), true);
        foreach ($ia['isos'] as $iso) {
            $id = $iso['id'];
            $this->ids[] = $id;
            $this->isos[$id] = $iso;
        }
        $this->total_isos = $ia['meta']['total'];
    }
}
