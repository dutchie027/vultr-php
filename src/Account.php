<?php

namespace dutchie027\Vultr;

class Account
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
     * getAccountInfo
     * Gets account info
     *
     *
     * @return string
     *
     */
    public function getAccountInfo()
    {
        return $this->api->makeAPICall('GET', $this->api::ACCOUNT_URL);
    }
}
