<?php

/**
 * PHP Wrapper to Interact with Vultr 2.0 API
 *
 * @version 2.0
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @see     https://github.com/dutche027/vultr-php
 * @see     https://packagist.org/packages/dutchie027/vultr
 * @see     https://www.vultr.com/api/v2
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
     */
    public function listPublicISOs(): string
    {
        return $this->api->makeAPICall('GET', $this->api::ISO_PUBLIC_URL);
    }

    /**
     * listISOs
     * Lists ISO Files
     */
    public function listISOs(): string
    {
        return $this->api->makeAPICall('GET', $this->api::ISO_URL);
    }

    /**
     * deleteISO
     * Lists ISO Files
     */
    public function deleteISO(string $id): string
    {
        return $this->api->makeAPICall('DELETE', $this->api::ISO_URL . '/' . $id);
    }

    /**
     * getISO
     * Get ISO Information
     */
    public function getISO(string $id): string
    {
        return $this->api->makeAPICall('GET', $this->api::ISO_URL . '/' . $id);
    }

    /**
     * createISO
     * Create ISO
     */
    public function createISO(string $url): string
    {
        $ba['url'] = $url;
        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $this->api::ISO_URL, $body);
    }

    /**
     * loadISOs
     * Loads ISO Information in to arrays
     */
    public function loadISOs(): void
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
