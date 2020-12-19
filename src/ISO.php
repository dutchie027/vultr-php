<?php

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
    }

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
}
