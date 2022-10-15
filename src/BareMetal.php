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

class BareMetal
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of Bare Metal IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Array of Bare Metals
     *
     * @var array<string>
     */
    public $metals = [];

    /**
     * Total Count of Bare Metal
     *
     * @var int
     */
    protected $bare_metal_count;

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadBareMetal();
    }

    /**
     * listBareMetalInstances
     * List all Bare Metal instances in your account.
     *
     * @see https://www.vultr.com/api/v2/#operation/list-baremetals
     */
    public function listBareMetalInstances(): string
    {
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL);
    }

    /**
     * loadBareMetal
     * Loads Bare Metal in to arrays
     */
    public function loadBareMetal(): void
    {
        $bma = json_decode($this->listBareMetalInstances(), true);

        foreach ($bma['bare_metals'] as $metal) {
            $id = $metal['id'];
            $this->ids[] = $id;
            $this->metals[$id] = $metal;
        }
        $this->bare_metal_count = $bma['meta']['total'];
    }

    /**
     * getBareMetal
     * Get Instance Information
     *
     * @see https://www.vultr.com/api/v2/#operation/get-baremetal
     */
    public function getIngetBareMetalstance(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id);
    }

    /**
     * updateBareMetal
     * Update a Bare Metal instance.
     * All attributes are optional.
     * If not set, the attributes will retain their original values.
     *
     * @param array<mixed> $oa
     *
     * @see https://www.vultr.com/api/v2/#operation/update-baremetal
     */
    public function updateBareMetal(array $oa): string
    {
        $this->checkBareMetalId($oa['id']);
        $hasOS = false;
        $ba = [];

        if (isset($oa['os_id']) && in_array($oa['os_id'], $this->api->operatingSystems()->ids, true)) {
            $hasOS = true;
            $ba['os_id'] = $oa['os_id'];
        }

        if (!$hasOS && isset($oa['app_id']) && in_array($oa['app_id'], $this->api->applications()->ids, true)) {
            $hasOS = true;
            $ba['app_id'] = $oa['app_id'];
        }

        if (isset($oa['enable_ipv6']) && is_bool($oa['enable_ipv6'])) {
            $ba['enable_ipv6'] = ($oa['enable_ipv6']) ? true : false;
        }

        if (isset($oa['user_data'])) {
            $ba['user_data'] = $oa['user_data'];
        }
        (isset($oa['tag'])) ? $ba['tag'] = $oa['tag'] : null;
        (isset($oa['label'])) ? $ba['label'] = $oa['label'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('PATCH', $this->api::BARE_METAL_URL . '/' . $oa['id'], $body);
    }

    /**
     * createBareMetal
     * Create a new Bare Metal instance in a region with the desired plan.
     *
     * @param array<mixed> $oa
     *
     * @see https://www.vultr.com/api/v2/#operation/create-baremetal
     */
    public function createBareMetal(array $oa): string
    {
        $hasOS = false;

        if (!isset($oa['region']) || !in_array($oa['region'], $this->api->regions()->ids, true)) {
            throw new InvalidParameterException('Invalid Region');
        }
        $ba['region'] = $oa['region'];

        if (!isset($oa['plan']) || !in_array($oa['plan'], $this->api->plans()->metal_ids, true)) {
            throw new InvalidParameterException('Invalid Plan');
        }
        $ba['plan'] = $oa['plan'];

        if (isset($oa['os_id']) && in_array($oa['os_id'], $this->api->operatingSystems()->ids, true)) {
            $hasOS = true;
            $ba['os_id'] = $oa['os_id'];
        }

        if (!$hasOS && isset($oa['snapshot_id']) && in_array($oa['snapshot_id'], $this->api->snapshots()->ids, true)) {
            $hasOS = true;
            $ba['snapshot_id'] = $oa['snapshot_id'];
        }

        if (!$hasOS && isset($oa['app_id']) && in_array($oa['app_id'], $this->api->applications()->ids, true)) {
            $hasOS = true;
            $ba['app_id'] = $oa['app_id'];
        }

        if (!$hasOS) {
            throw new InvalidParameterException('A Valid OS (os_id, iso_id, snapshot_id or app_id) is missing');
        }
        (isset($oa['label'])) ? $ba['label'] = $oa['label'] : null;
        (isset($oa['tag'])) ? $ba['tag'] = $oa['tag'] : null;

        if (isset($oa['enable_ipv6']) && $oa['enable_ipv6'] == true) {
            $ba['enable_ipv6'] = true;
        } else {
            $ba['enable_ipv6'] = false;
        }

        if (isset($oa['activation_email']) && $oa['activation_email'] == false) {
            $ba['activation_email'] = false;
        } else {
            $ba['activation_email'] = true;
        }

        if (isset($oa['sshkey_id']) && in_array($oa['sshkey_id'], $this->api->sshKeys()->ids, true)) {
            $ba['sshkey_id'] = $oa['sshkey_id'];
        } elseif (isset($oa['sshkey_id']) && !in_array($oa['sshkey_id'], $this->api->sshKeys()->ids, true)) {
            throw new InvalidParameterException("You provided an SSH Key ID and it's not part of your account");
        }

        if (isset($oa['script_id']) && in_array($oa['script_id'], $this->api->startupScripts()->ids, true)) {
            $ba['script_id'] = $oa['script_id'];
        } elseif (isset($oa['script_id']) && !in_array($oa['script_id'], $this->api->startupScripts()->ids, true)) {
            throw new InvalidParameterException("You provided an Startup Script and it's not part of your account");
        }

        if (isset($oa['reserved_ipv4']) && in_array($oa['reserved_ipv4'], $this->api->reservedIPs()->ids, true)) {
            $ba['reserved_ipv4'] = $oa['reserved_ipv4'];
        } elseif (isset($oa['reserved_ipv4']) && !in_array($oa['reserved_ipv4'], $this->api->reservedIPs()->ids, true)) {
            throw new InvalidParameterException("You provided a Reserved IP and it's not part of your account");
        }

        if (isset($oa['user_data'])) {
            $ba['user_data'] = $oa['user_data'];
        }

        if (isset($oa['hostname'])) {
            $ba['hostname'] = $oa['hostname'];
        } else {
            $ba['hostname'] = strtolower($this->api->pGenRandomString(10));
        }
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL, $body);
    }

    /**
     * deleteBareMetal
     * Delete a Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/delete-baremetal
     */
    public function deleteBareMetal(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('DELETE', $this->api::BARE_METAL_URL . '/' . $id);
    }

    /**
     * bareMetalIPv4Addresses
     * Get the IPv4 information for the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/get-ipv6-baremetal
     */
    public function bareMetalIPv4Addresses(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id . '/ipv4');
    }

    /**
     * bareMetalIPv6Addresses
     * Get the IPv6 information for the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/get-ipv6-baremetal
     */
    public function bareMetalIPv6Addresses(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id . '/ipv6');
    }

    /**
     * startBareMetal
     * Start the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/start-baremetal
     */
    public function startBareMetal(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/' . $id . '/start');
    }

    /**
     * rebootBareMetal
     * Reboot the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/reboot-baremetal
     */
    public function rebootBareMetal(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/' . $id . '/reboot');
    }

    /**
     * reinstallBareMetal
     * Reinstall the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/reinstall-baremetal
     */
    public function reinstallBareMetal(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/' . $id . '/reinstall');
    }

    /**
     * haltBareMetal
     * Halt the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/halt-baremetal
     */
    public function haltBareMetal(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/' . $id . '/halt');
    }

    /**
     * bareMetalBandwidth
     * Get bandwidth information for the Bare Metal instance.
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bandwidth-baremetal
     */
    public function bareMetalBandwidth(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id . '/bandwidth');
    }

    /**
     * haltBareMetals
     * Halt Bare Metals.
     *
     * @param array<string,string> $oa
     *
     * @see https://www.vultr.com/api/v2/#operation/halt-baremetals
     */
    public function haltBareMetals(array $oa): string
    {
        foreach ($oa as $inst) {
            $this->checkBareMetalId($inst);
        }
        $ba['baremetal_ids'] = $oa;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/halt', $body);
    }

    /**
     * rebootBareMetals
     * Reboot Bare Metals.
     *
     * @param array<string,string> $oa
     *
     * @see https://www.vultr.com/api/v2/#operation/reboot-bare-metals
     */
    public function rebootBareMetals(array $oa): string
    {
        foreach ($oa as $inst) {
            $this->checkBareMetalId($inst);
        }
        $ba['baremetal_ids'] = $oa;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/reboot', $body);
    }

    /**
     * startBareMetals
     * Start Bare Metals.
     *
     * @param array<string,string> $oa
     *
     * @see https://www.vultr.com/api/v2/#operation/start-bare-metals
     */
    public function startBareMetals(array $oa): string
    {
        foreach ($oa as $inst) {
            $this->checkBareMetalId($inst);
        }
        $ba['baremetal_ids'] = $oa;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . '/start', $body);
    }

    /**
     * getBareMetalUserData
     * Get the user-supplied, base64 encoded user data for a Bare Metal.
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bare-metal-userdata
     */
    public function getBareMetalUserData(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id . '/user-data');
    }

    /**
     * getAvailableBareMetalUpgrades
     * Get available upgrades for a Bare Metal
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bare-metals-upgrades
     */
    public function getAvailableBareMetalUpgrades(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id . '/upgrades');
    }

    /**
     * getVNCURLForABareMetal
     * Get the VNC URL for a Bare Metal
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bare-metal-vnc
     */
    public function getVNCURLForABareMetal(string $id): string
    {
        $this->checkBareMetalId($id);

        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . '/' . $id . '/vnc');
    }

    /**
     * checkBareMetalId
     * Checks's if an Metal ID is valid or not
     */
    public function checkBareMetalId(string $id): bool
    {
        if (in_array($id, $this->ids, true)) {
            return true;
        }

        throw new InvalidParameterException('Instance Not Found');
    }
}
