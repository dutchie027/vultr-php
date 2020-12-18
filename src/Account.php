<?php

namespace dutchie027\Vultr;

class Account
{

    protected $api;

    public function __construct(API $api)
    {
        $this->api = $api;
    }

    public function getAccountInfo()
    {
        return $this->api->makeAPICall('GET', $this->api::ACCOUNT_URL);
    }
}