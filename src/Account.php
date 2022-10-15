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
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * getAccountInfo
     * Gets account info
     */
    public function getAccountInfo(): Psr7\Stream
    {
        return $this->api->makeAPICall('GET', $this->api::ACCOUNT_URL);
    }
}
