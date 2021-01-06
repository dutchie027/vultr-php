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

class PrivateNetworks
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

     /**
     * Array of All Private Network IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Default label to use when creating script or updating Network
     *
     * @var string
     */
    protected $d_label = "";

    /**
     * Array of Private Network Information
     *
     * @var array
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
     *
     * @param object $api API
     *
     * @return object
     *
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadPrivateNetworks();
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
     * listPrivateNetworks
     * Lists Private Networks
     *
     *
     * @return string
     *
     */
    public function listPrivateNetworks()
    {
        return $this->api->makeAPICall('GET', $this->api::PRIVATE_NETWORKS_URL);
    }

    /**
     * deletePrivateNetwork
     * Deletes Private Network
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function deletePrivateNetwork($id)
    {
        return $this->api->makeAPICall('DELETE', $this->api::PRIVATE_NETWORKS_URL . "/" . $id);
    }

    /**
     * getPrivateNetwork
     * Get Private Network Information
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function getPrivateNetwork($id)
    {
        return $this->api->makeAPICall('GET', $this->api::PRIVATE_NETWORKS_URL . "/" . $id);
    }

     /**
     * loadPrivateNetworks
     * Loads Startup Script Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadPrivateNetworks()
    {
        $pna = json_decode($this->listPrivateNetworks(), true);
        foreach ($pna['networks'] as $net) {
            $id = $net['id'];
            $this->ids[] = $id;
            $this->privateNetwork[$id]['region'] = $net['region'];
            $this->privateNetwork[$id]['date_created'] = $net['date_created'];
            $this->privateNetwork[$id]['description'] = $net['description'];
            $this->privateNetwork[$id]['v4_subnet'] = $net['v4_subnet'];
            $this->privateNetwork[$id]['v4_subnet_mask'] = $net['v4_subnet_mask'];
        }
        $this->total_private_networks = $pna['meta']['total'];
    }

    /**
     * updatePrivateNetwork
     * Updates description of Private Network
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function updatePrivateNetwork($oa)
    {
        if (in_array($oa['id'], $this->ids)) {
            $url = $this->api::PRIVATE_NETWORKS_URL . "/" . $oa['id'];
        } else {
            print "That Private Network ID isn't associated with your account";
            exit;
        }
        $ba['description'] = $this->d_label;
        (isset($oa['description'])) ? $ba['description'] = $oa['description'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('PUT', $url, $body);
    }

    /**
     * createPrivateNetwork
     * Creates a Private Network
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function createPrivateNetwork($oa)
    {
        if (!isset($oa['region']) || !in_array($oa['region'], $this->api->regions()->ids)) {
            print "Invalid Region";
            exit;
        } else {
            $ba['region'] = $oa['region'];
        }
        if (isset($oa['subnet']) && $this->checkPrivateIP($oa['subnet'])) {
            $ba['v4_subnet'] = $oa['subnet'];
        } else {
            print "Subnet is invalid. Must be an IP address and must meet RFC Standard for Private Networks.";
            exit;
        }
        if (isset($oa['mask']) && $oa['mask'] > 0 && $oa['mask'] < 32) {
            $ba['v4_subnet_mask'] = $oa['mask'];
        } else {
            print "Subnet mask must be between 1 and 31 (you can't have a /32 private network)";
        }
        (isset($oa['description'])) ? $ba['description'] = $oa['description'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::PRIVATE_NETWORKS_URL, $body);
    }

    private function checkPrivateIP($ip)
    {
        $pri_addrs = array (
            '10.0.0.0|10.255.255.255', // single class A network
            '172.16.0.0|172.31.255.255', // 16 contiguous class B network
            '192.168.0.0|192.168.255.255', // 256 contiguous class C network
            '127.0.0.0|127.255.255.255' // localhost
        );
        $long_ip = ip2long($ip);
        if ($long_ip != -1) {
            foreach ($pri_addrs as $pri_addr) {
                list ($start, $end) = explode('|', $pri_addr);
                if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end)) {
                     return true;
                }
            }
        }
        return false;
    }
}
