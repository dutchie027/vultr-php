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

use dutchie027\Vultr\Exceptions\InvalidParameterException;

class PrivateNetworks
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of All Private Network IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Default label to use when creating script or updating Network
     *
     * @var string
     */
    protected $d_label = '';

    /**
     * Array of Private Network Information
     *
     * @var array<string>
     */
    public $privateNetwork = [];

    /**
     * Count of Total Private Networks
     *
     * @var int
     */
    protected $total_private_networks;

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadPrivateNetworks();
    }

    /**
     * listPrivateNetworks
     * Lists Private Networks
     */
    public function listPrivateNetworks(): string
    {
        return $this->api->makeAPICall('GET', $this->api::PRIVATE_NETWORKS_URL);
    }

    /**
     * deletePrivateNetwork
     * Deletes Private Network
     */
    public function deletePrivateNetwork(string $id): string
    {
        return $this->api->makeAPICall('DELETE', $this->api::PRIVATE_NETWORKS_URL . '/' . $id);
    }

    /**
     * getPrivateNetwork
     * Get Private Network Information
     */
    public function getPrivateNetwork(string $id): string
    {
        return $this->api->makeAPICall('GET', $this->api::PRIVATE_NETWORKS_URL . '/' . $id);
    }

    /**
     * loadPrivateNetworks
     * Loads Startup Script Information in to arrays
     */
    public function loadPrivateNetworks(): void
    {
        $pna = json_decode($this->listPrivateNetworks(), true);

        foreach ($pna['networks'] as $net) {
            $id = $net['id'];
            $this->ids[] = $id;
            $this->privateNetwork[$id] = $net;
        }
        $this->total_private_networks = $pna['meta']['total'];
    }

    /**
     * updatePrivateNetwork
     * Updates description of Private Network
     * @param array<string,string> $oa
     */
    public function updatePrivateNetwork(array $oa): string
    {
        if (in_array($oa['id'], $this->ids, true)) {
            $url = $this->api::PRIVATE_NETWORKS_URL . '/' . $oa['id'];
        } else {
            throw new InvalidParameterException("That Private Network ID isn't associated with your account");
        }
        $ba['description'] = $this->d_label;
        (isset($oa['description'])) ? $ba['description'] = $oa['description'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('PUT', $url, $body);
    }

    /**
     * createPrivateNetwork
     * Creates a Private Network
     * @param array<string,string> $oa
     */
    public function createPrivateNetwork(array $oa): string
    {
        if (!isset($oa['region']) || !in_array($oa['region'], $this->api->regions()->ids, true)) {
            throw new InvalidParameterException('Invalid Region');
        }
        $ba['region'] = $oa['region'];

        if (isset($oa['subnet']) && $this->checkPrivateIP($oa['subnet'])) {
            $ba['v4_subnet'] = $oa['subnet'];
        } else {
            throw new InvalidParameterException('Subnet is invalid. Must be an IP address and must meet RFC Standard for Private Networks.');
        }

        if (isset($oa['mask']) && $oa['mask'] > 0 && $oa['mask'] < 32) {
            $ba['v4_subnet_mask'] = $oa['mask'];
        } else {
            throw new InvalidParameterException("Subnet mask must be between 1 and 31 (you can't have a /32 private network)");
        }
        (isset($oa['description'])) ? $ba['description'] = $oa['description'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::PRIVATE_NETWORKS_URL, $body);
    }

    private function checkPrivateIP(string $ip): bool
    {
        $pri_addrs = [
            '10.0.0.0|10.255.255.255', // single class A network
            '172.16.0.0|172.31.255.255', // 16 contiguous class B network
            '192.168.0.0|192.168.255.255', // 256 contiguous class C network
            '127.0.0.0|127.255.255.255', // localhost
        ];
        $long_ip = ip2long($ip);

        if ($long_ip != -1) {
            foreach ($pri_addrs as $pri_addr) {
                [$start, $end] = explode('|', $pri_addr);

                if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end)) {
                    return true;
                }
            }
        }

        return false;
    }
}
