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

class BareMetal
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of Bare Metal IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Array of Bare Metals
     *
     * @var array
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
     *
     * @param object $api API
     *
     * @return object
     *
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
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/list-baremetals
     *
     */
    public function listBareMetalInstances()
    {
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL);
    }

    /**
     * loadBareMetal
     * Loads Bare Metal in to arrays
     *
     *
     * @return void
     *
     */
    public function loadBareMetal()
    {
        $bma = json_decode($this->listBareMetalInstances(), true);
        foreach ($bma['bare_metals'] as $metal) {
            $id = $metal['id'];
            $this->ids[] = $id;
            $this->metals[$id]['os'] = $metal['os'];
            $this->metals[$id]['ram'] = $metal['ram'];
            $this->metals[$id]['disk'] = $metal['disk'];
            $this->metals[$id]['main_ip'] = $metal['main_ip'];
            $this->metals[$id]['cpu_count'] = $metal['cpu_count'];
            $this->metals[$id]['region'] = $metal['region'];
            $this->metals[$id]['default_password'] = $metal['default_password'];
            $this->metals[$id]['date_created'] = $metal['date_created'];
            $this->metals[$id]['status'] = $metal['status'];
            $this->metals[$id]['netmask_v4'] = $metal['netmask_v4'];
            $this->metals[$id]['gateway_v4'] = $metal['gateway_v4'];
            $this->metals[$id]['plan'] = $metal['plan'];
            $this->metals[$id]['v6_network'] = $metal['v6_network'];
            $this->metals[$id]['v6_main_ip'] = $metal['v6_main_ip'];
            $this->metals[$id]['v6_network_size'] = $metal['v6_network_size'];
            $this->metals[$id]['label'] = $metal['label'];
            $this->metals[$id]['mac_address'] = $metal['mac_address'];
            $this->metals[$id]['tag'] = $metal['tag'];
            $this->metals[$id]['os_id'] = $metal['os_id'];
            $this->metals[$id]['app_id'] = $metal['app_id'];
            foreach ($metal['features'] as $feat) {
                $this->metals[$id]['feature'][] = $feat;
            }
        }
        $this->bare_metal_count = $bma['meta']['total'];
    }

    /**
     * getBareMetal
     * Get Instance Information
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/get-baremetal
     *
     */
    public function getIngetBareMetalstance($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id);
    }

    /**
     * updateBareMetal
     * Update a Bare Metal instance.
     * All attributes are optional.
     * If not set, the attributes will retain their original values.
     *
     * @param $oa array
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/update-baremetal
     *
     */
    public function updateBareMetal($oa)
    {
        $this->checkBareMetalId($oa['id']);
        $hasOS = false;
        if (!$hasOS && isset($oa['os_id']) && in_array($oa['os_id'], $this->api->operatingSystems()->ids)) {
            $hasOS = true;
            $ba['os_id'] = $oa['os_id'];
        }
        if (!$hasOS && isset($oa['app_id']) && in_array($oa['app_id'], $this->api->applications()->ids)) {
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
        $body = json_encode($ba);
        return $this->api->makeAPICall('PATCH', $this->api::BARE_METAL_URL . "/" . $oa['id'], $body);
    }

    /**
     * createBareMetal
     * Create a new Bare Metal instance in a region with the desired plan.
     *
     * @param $oa array
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/create-baremetal
     *
     */
    public function createBareMetal($oa)
    {
        $hasOS = false;
        if (!isset($oa['region']) || !in_array($oa['region'], $this->api->regions()->ids)) {
            print "Invalid Region";
            exit;
        } else {
            $ba['region'] = $oa['region'];
        }
        if (!isset($oa['plan']) || !in_array($oa['plan'], $this->api->plans()->metal_ids)) {
            print "Invalid Plan";
            exit;
        } else {
            $ba['plan'] = $oa['plan'];
        }
        if (!$hasOS && isset($oa['os_id']) && in_array($oa['os_id'], $this->api->operatingSystems()->ids)) {
            $hasOS = true;
            $ba['os_id'] = $oa['os_id'];
        }
        if (!$hasOS && isset($oa['snapshot_id']) && in_array($oa['snapshot_id'], $this->api->snapshots()->ids)) {
            $hasOS = true;
            $ba['snapshot_id'] = $oa['snapshot_id'];
        }
        if (!$hasOS && isset($oa['app_id']) && in_array($oa['app_id'], $this->api->applications()->ids)) {
            $hasOS = true;
            $ba['app_id'] = $oa['app_id'];
        }
        if (!$hasOS) {
            print "A Valid OS (os_id, iso_id, snapshot_id or app_id) is missing";
            exit;
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
        if (isset($oa['sshkey_id']) && in_array($oa['sshkey_id'], $this->api->sshKeys()->ids)) {
            $ba['sshkey_id'] = $oa['sshkey_id'];
        } elseif (isset($oa['sshkey_id']) && !in_array($oa['sshkey_id'], $this->api->sshKeys()->ids)) {
            print "You provided an SSH Key ID and it's not part of your account";
            exit;
        }
        if (isset($oa['script_id']) && in_array($oa['script_id'], $this->api->startupScripts()->ids)) {
            $ba['script_id'] = $oa['script_id'];
        } elseif (isset($oa['script_id']) && !in_array($oa['script_id'], $this->api->startupScripts()->ids)) {
            print "You provided an Startup Script and it's not part of your account";
            exit;
        }
        if (isset($oa['reserved_ipv4']) && in_array($oa['reserved_ipv4'], $this->api->reservedIPs()->ids)) {
            $ba['reserved_ipv4'] = $oa['reserved_ipv4'];
        } elseif (isset($oa['reserved_ipv4']) && !in_array($oa['reserved_ipv4'], $this->api->reservedIPs()->ids)) {
            print "You provided a Reserved IP and it's not part of your account";
            exit;
        }
        if (isset($oa['user_data'])) {
            $ba['user_data'] = $oa['user_data'];
        }
        if (isset($oa['hostname'])) {
            $ba['hostname'] = $oa['hostname'];
        } else {
            $ba['hostname'] = strtolower($this->api->pGenRandomString(10));
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL, $body);
    }

    /**
     * deleteBareMetal
     * Delete a Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/delete-baremetal
     *
     */
    public function deleteBareMetal($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('DELETE', $this->api::BARE_METAL_URL . "/" . $id);
    }

    /**
     * bareMetalIPv4Addresses
     * Get the IPv4 information for the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/get-ipv6-baremetal
     *
     */
    public function bareMetalIPv4Addresses($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id . "/ipv4");
    }

    /**
     * bareMetalIPv6Addresses
     * Get the IPv6 information for the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/get-ipv6-baremetal
     *
     */
    public function bareMetalIPv6Addresses($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id . "/ipv6");
    }

    /**
     * startBareMetal
     * Start the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/start-baremetal
     *
     */
    public function startBareMetal($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/" . $id . "/start");
    }

    /**
     * rebootBareMetal
     * Reboot the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/reboot-baremetal
     *
     */
    public function rebootBareMetal($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/" . $id . "/reboot");
    }

    /**
     * reinstallBareMetal
     * Reinstall the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/reinstall-baremetal
     *
     */
    public function reinstallBareMetal($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/" . $id . "/reinstall");
    }

    /**
     * haltBareMetal
     * Halt the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/halt-baremetal
     *
     */
    public function haltBareMetal($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/" . $id . "/halt");
    }

    /**
     * bareMetalBandwidth
     * Get bandwidth information for the Bare Metal instance.
     *
     * @var string $id
     *
     * @return string
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bandwidth-baremetal
     *
     */
    public function bareMetalBandwidth($id)
    {
        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id . "/bandwidth");
    }

    /**
     * haltBareMetals
     * Halt Bare Metals.
     *
     * @param $oa array
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/halt-baremetals
     *
     */
    public function haltBareMetals($oa)
    {
        foreach ($oa as $inst) {
            $this->checkBareMetalId($inst);
        }
        $ba['baremetal_ids'] = $oa;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/halt", $body);
    }

    /**
     * rebootBareMetals
     * Reboot Bare Metals.
     *
     * @param $oa array
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/reboot-bare-metals
     *
     */
    public function rebootBareMetals($oa)
    {
        foreach ($oa as $inst) {
            $this->checkBareMetalId($inst);
        }
        $ba['baremetal_ids'] = $oa;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/reboot", $body);
    }

    /**
     * startBareMetals
     * Start Bare Metals.
     *
     * @param $oa array
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/start-bare-metals
     *
     */
    public function startBareMetals($oa)
    {
        foreach ($oa as $inst) {
            $this->checkBareMetalId($inst);
        }
        $ba['baremetal_ids'] = $oa;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::BARE_METAL_URL . "/start", $body);
    }

    /**
     * getBareMetalUserData
     * Get the user-supplied, base64 encoded user data for a Bare Metal.
     *
     * @param $id string
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bare-metal-userdata
     *
     */
    public function getBareMetalUserData($id)
    {

        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id . "/user-data");
    }

    /**
     * getAvailableBareMetalUpgrades
     * Get available upgrades for a Bare Metal
     *
     * @param $id string
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bare-metals-upgrades
     *
     */
    public function getAvailableBareMetalUpgrades($id)
    {

        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id . "/upgrades");
    }

    /**
     * getVNCURLForABareMetal
     * Get the VNC URL for a Bare Metal
     *
     * @param $id string
     *
     * @return void
     *
     * @see https://www.vultr.com/api/v2/#operation/get-bare-metal-vnc
     *
     */
    public function getVNCURLForABareMetal($id)
    {

        $this->checkBareMetalId($id);
        return $this->api->makeAPICall('GET', $this->api::BARE_METAL_URL . "/" . $id . "/vnc");
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
     * checkBareMetalId
     * Checks's if an Metal ID is valid or not
     *
     * @var string $id
     *
     * @return bool
     *
     */
    public function checkBareMetalId($id)
    {
        if (in_array($id, $this->ids)) {
            return true;
        } else {
            print "Instance Not Found";
            exit;
        }
    }
}
