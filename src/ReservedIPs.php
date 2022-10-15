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

class ReservedIPs
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of All Reserved IP IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Array of IP Information
     *
     * @var array
     */
    public $reservedIP = [];

    /**
     * Count of Total IPs
     *
     * @var int
     */
    protected $total_reserved_ips;

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
        $this->loadReservedIPs();
    }

    /**
     * listReservedIPs
     * List all Reserved IPs in your account.
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/list-reserved-ips
     */
    public function listReservedIPs()
    {
        return $this->api->makeAPICall('GET', $this->api::RESERVED_IPS_URL);
    }

    /**
     * loadReservedIPs
     * Loads Reserved IP Information in to arrays
     */
    public function loadReservedIPs()
    {
        $ipa = json_decode($this->listReservedIPs(), true);

        foreach ($ipa['reserved_ips'] as $key) {
            $id = $key['id'];
            $this->ids[] = $id;
            $this->reservedIP[$id] = $key;
        }
        $this->total_reserved_ips = $ipa['meta']['total'];
    }

    /**
     * deleteReservedIP
     * Delete a Reserved IP.
     *
     * @var string
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/delete-reserved-ip
     */
    public function deleteReservedIP($id)
    {
        return $this->api->makeAPICall('DELETE', $this->api::RESERVED_IPS_URL . '/' . $id);
    }

    /**
     * getReservedIP
     * Get information about a Reserved IP.
     *
     * @var string
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/get-reserved-ip
     */
    public function getReservedIP($id)
    {
        return $this->api->makeAPICall('GET', $this->api::RESERVED_IPS_URL . '/' . $id);
    }

    /**
     * createReservedIP
     * Create a new Reserved IP.
     * The region and ip_type attributes are required.
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/create-reserved-ip
     */
    public function createReservedIP($oa)
    {
        if (!isset($oa['region']) || !in_array($oa['region'], $this->api->regions()->ids, true)) {
            throw new InvalidParameterException('Invalid Region');
        }
        $ba['region'] = $oa['region'];

        if (!isset($oa['ip_type']) || !preg_match('/v[46]/', $oa['ip_type'])) {
            throw new InvalidParameterException('Invalid IP Type');
        }
        $ba['ip_type'] = $oa['ip_type'];

        (isset($oa['label'])) ? $ba['label'] = $oa['label'] : null;
        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $this->api::RESERVED_IPS_URL, $body);
    }

    /**
     * attachReservedIP
     * Attach a Reserved IP to an instance_id.
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/attach-reserved-ip
     */
    public function attachReservedIP($oa)
    {
        if (!isset($oa['instance_id']) || !$this->api->instances()->checkInstanceId($oa['instance_id'])) {
            throw new InvalidParameterException('Invalid or Missing Instance ID');
        }
        $ba['instance_id'] = $oa['instance_id'];

        if (!isset($oa['reserved_ip']) || !$this->checkReservedIP($oa['reserved_ip'])) {
            throw new InvalidParameterException('Invalid or Missing Instance IP');
        }
        $ip = $oa['reserved_ip'];

        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $this->api::RESERVED_IPS_URL . '/' . $ip . '/attach', $body);
    }

    /**
     * detachReservedIP
     * Attach a Reserved IP to an instance_id.
     *
     * @param string $ip
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/detach-reserved-ip
     */
    public function detachReservedIP($ip)
    {
        $this->checkReservedIP($ip);

        return $this->api->makeAPICall('POST', $this->api::RESERVED_IPS_URL . '/' . $ip . '/detach');
    }

    /**
     * convertInstanceIPToReservedIP
     * Convert ip_address on instance_id into a Reserved IP.
     *
     * @param array $oa
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/convert-reserved-ip
     */
    public function convertInstanceIPToReservedIP($oa)
    {
        if (!isset($oa['ip_address']) || !$this->checkReservedIP($oa['ip_address'])) {
            throw new InvalidParameterException('Invalid or Missing IP');
        }
        $ba['ip_address'] = $oa['ip_address'];

        (isset($oa['label'])) ? $ba['label'] = $oa['label'] : null;
        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $this->api::RESERVED_IPS_URL . '/convert', $body);
    }

    /**
     * checkReservedIP
     * Checks's if an IP ID is valid or not
     *
     * @var string
     *
     * @return bool
     */
    public function checkReservedIP($id)
    {
        if (in_array($id, $this->ids, true)) {
            return true;
        }

        throw new InvalidParameterException('IP Not Found');
    }
}
