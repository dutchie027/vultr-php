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

class Instances
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array containing Instance IDs
     *
     * @var array
     */
    public $ids = [];

    public $instance = [];

    /**
     * Total Count of Instances
     *
     * @var int
     */
    protected $total_instances;

    /**
     * __construct
     * Main Construct - Loads Instances in to arrays and creates reference
     * from main API class
     *
     * @param $api
     *
     * @return void
     *
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadInstances();
    }

    /**
     * listInstances
     * Lists All Instances
     *
     *
     * @return string
     *
     */
    public function listInstances()
    {
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL);
    }


    /**
     * createInstance
     * Creates an Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function createInstance($oa)
    {
        $hasOS = false;
        if (!isset($oa['region']) || !in_array($oa['region'], $this->api->regions()->ids)) {
            print "Invalid Region";
            exit;
        } else {
            $ba['region'] = $oa['region'];
        }
        if (!isset($oa['plan']) || !in_array($oa['plan'], $this->api->plans()->ids)) {
            print "Invalid Plan";
            exit;
        } else {
            $ba['plan'] = $oa['plan'];
        }
        if (!$hasOS && isset($oa['os_id']) && in_array($oa['os_id'], $this->api->operatingSystems()->ids)) {
            $hasOS = true;
            $ba['os_id'] = $oa['os_id'];
        }
        if (!$hasOS && isset($oa['iso_id']) && in_array($oa['iso_id'], $this->api->iso()->ids)) {
            $hasOS = true;
            $ba['iso_id'] = $oa['iso_id'];
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
        (isset($oa['ipxe_chain_url'])) ? $ba['ipxe_chain_url'] = $oa['ipxe_chain_url'] : null;
        (isset($oa['label'])) ? $ba['label'] = $oa['label'] : null;
        (isset($oa['tag'])) ? $ba['tag'] = $oa['tag'] : null;
        if (isset($oa['enable_ipv6']) && $oa['enable_ipv6'] == true) {
            $ba['enable_ipv6'] = true;
        } else {
            $ba['enable_ipv6'] = false;
        }
        if (isset($oa['backups']) && $oa['backups'] == true) {
            $ba['backups'] = "enabled";
        } else {
            $ba['backups'] = "disabled";
        }
        if (isset($oa['ddos_protection']) && $oa['ddos_protection'] == true) {
            if (!in_array($oa['region'], $this->api->regions()->ddos_ids)) {
                print "You chose to set DDOS, but the region is not capable of it";
                exit;
            }
            $ba['ddos_protection'] = true;
        } else {
            $ba['ddos_protection'] = false;
        }
        if (isset($oa['activation_email']) && $oa['activation_email'] == false) {
            $ba['activation_email'] = false;
        } else {
            $ba['activation_email'] = true;
        }
        if (isset($oa['enable_private_network']) && $oa['enable_private_network'] == true) {
            if (!isset($oa['attach_private_network']) || !is_array($oa['attach_private_network'])) {
                print "You chose to enable private networks but you didn't provide one or it's not an array";
                exit;
            }
            foreach ($oa['attach_private_network'] as $pnet) {
                if (!in_array($pnet, $this->api->privateNetworks()->ids)) {
                    print "Private Network Not Found";
                    exit;
                }
            }
            $ba['enable_private_network'] = true;
            $ba['attach_private_network'] = $oa['attach_private_network'];
        } else {
            $ba['enable_private_network'] = false;
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
        if (isset($oa['firewall_group_id']) && in_array($oa['firewall_group_id'], $this->api->firewalls()->ids)) {
            $ba['firewall_group_id'] = $oa['firewall_group_id'];
        } elseif (isset($oa['firewall_group_id']) && !in_array($oa['firewall_group_id'], $this->api->firewalls()->ids)) {
            print "You provided a Firewall ID that is not part of your account";
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
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL, $body);
    }

    /**
     * loadInstances
     * Loads Instances in to Array
     *
     *
     * @return void
     *
     */
    public function loadInstances()
    {
        $ia = json_decode($this->listInstances(), true);
        foreach ($ia['instances'] as $inst) {
            $id = $inst['id'];
            $this->ids[] = $id;
            $this->instance[$id] = $inst;
        }
        $this->total_instances = $ia['meta']['total'];
    }

    /**
     * updateInstance
     * Updates an Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function updateInstance($oa)
    {
        $this->checkInstanceId($oa['id']);
        $hasOS = false;
        if (!$hasOS && isset($oa['os_id']) && in_array($oa['os_id'], $this->api->operatingSystems()->ids)) {
            $hasOS = true;
            $ba['os_id'] = $oa['os_id'];
        }
        if (!$hasOS && isset($oa['app_id']) && in_array($oa['app_id'], $this->api->applications()->ids)) {
            $hasOS = true;
            $ba['app_id'] = $oa['app_id'];
        }
        if (isset($oa['backups']) && is_bool($oa['backups'])) {
            $ba['backups'] = ($oa['backup']) ? "enabled" : "disabled";
        }
        if (isset($oa['enable_ipv6']) && is_bool($oa['enable_ipv6'])) {
            $ba['enable_ipv6'] = ($oa['enable_ipv6']) ? true : false;
        }
        if (isset($oa['firewall_group_id']) && in_array($oa['firewall_group_id'], $this->api->firewalls()->ids)) {
            $ba['firewall_group_id'] = $oa['firewall_group_id'];
        } elseif (isset($oa['firewall_group_id']) && !in_array($oa['firewall_group_id'], $this->api->firewalls()->ids)) {
            print "You provided a Firewall ID that is not part of your account";
            exit;
        }
        if (isset($oa['user_data'])) {
            $ba['user_data'] = $oa['user_data'];
        }
        (isset($oa['tag'])) ? $ba['tag'] = $oa['tag'] : null;
        if (isset($oa['plan']) && in_array($oa['plan'], $this->api->plans()->ids)) {
            $ba['plan'] = $oa['plan'];
        } elseif (isset($oa['plan'])) {
            print "Invalid Plan";
            exit;
        }
        if (isset($oa['ddos_protection']) && $oa['ddos_protection'] == true) {
            if (!in_array($oa['region'], $this->api->regions()->ddos_ids)) {
                print "You chose to set DDOS, but the region is not capable of it";
                exit;
            }
            $ba['ddos_protection'] = true;
        } elseif (isset($oa['ddos_protection']) && $oa['ddos_protection'] == false) {
            $ba['ddos_protection'] = false;
        }
        if (isset($oa['enable_private_network']) && $oa['enable_private_network'] == true) {
            if (!isset($oa['attach_private_network']) || !is_array($oa['attach_private_network'])) {
                print "You chose to enable private networks but you didn't provide one or it's not an array";
                exit;
            }
            foreach ($oa['attach_private_network'] as $pnet) {
                if (!in_array($pnet, $this->api->privateNetworks()->ids)) {
                    print "Private Network Not Found";
                    exit;
                }
            }
            $ba['enable_private_network'] = true;
            $ba['attach_private_network'] = $oa['attach_private_network'];
        }
        if (isset($oa['detach_private_network']) && is_array($oa['detach_private_network'])) {
            $ba['detach_private_network'] = $oa['detach_private_network'];
        } elseif (isset($oa['detach_private_network']) && !is_array($oa['detach_private_network'])) {
            print "Detatch Networks Is Not Valid";
            exit;
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('PATCH', $this->api::INSTANCES_URL . "/" . $oa['id'], $body);
    }

    /**
     * haltInstances
     * Halts an array of instances
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function haltInstances($oa)
    {
        foreach ($oa as $inst) {
            $this->checkInstanceId($inst);
        }
        $ba['instance_ids'] = $oa;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/halt", $body);
    }

    /**
     * rebootInstances
     * Reboots an array of instances
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function rebootInstances($oa)
    {
        foreach ($oa as $inst) {
            $this->checkInstanceId($inst);
        }
        $ba['instance_ids'] = $oa;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/reboot", $body);
    }

    /**
     * startInstances
     * Starts an array of instances
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function startInstances($oa)
    {
        foreach ($oa as $inst) {
            $this->checkInstanceId($inst);
        }
        $ba['instance_ids'] = $oa;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/start", $body);
    }

    /**
     * startInstance
     * Starts a single Instance ID
     *
     * @param $inst string
     *
     * @return void
     *
     */
    public function startInstance($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $inst . "/start");
    }

    /**
     * rebootInstance
     * Reboots a Single instance ID
     *
     * @param $inst string
     *
     * @return void
     *
     */
    public function rebootInstance($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $inst . "/reboot");
    }

    /**
     * reinstallInstance
     * Reinstalls a Single Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function reinstallInstance($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (isset($oa['hostname'])) {
            $ba['hostname'] = $oa['hostname'];
        } else {
            $ba['hostname'] = strtolower($this->api->pGenRandomString(10));
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/reinstall", $body);
    }

    /**
     * instanceBandwidth
     * Returns bandwidth used by an instance
     *
     * @param $inst string
     *
     * @return void
     *
     */
    public function instanceBandwidth($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/bandwidth");
    }

    /**
     * getInstanceNeighbors
     * Returns an Instance IDs Neighbors
     *
     * @param $inst string
     *
     * @return void
     *
     */
    public function getInstanceNeighbors($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/neighbors");
    }

    /**
     * listInstancePrivateNetworks
     * Lists the Private Networks of an Instance
     *
     * @param $inst string
     *
     * @return void
     *
     */
    public function listInstancePrivateNetworks($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/private-networks");
    }

    /**
     * getInstanceISOStatus
     * Gets the ISO Status from An Insnace
     *
     * @param $inst string
     *
     * @return void
     *
     */
    public function getInstanceISOStatus($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/iso");
    }

    /**
     * attachISOToInstance
     * Attaches an ISO to an Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function attachISOToInstance($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (!isset($oa['iso_id']) || !in_array($oa['iso_id'], $this->api->iso()->ids)) {
            print "ISO Not Found";
            exit;
        }
        $ba['iso_id'] = $oa['iso_id'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/iso/attach", $body);
    }

    /**
     * detachISOFromInstance
     * Detaches an ISO from an instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function detachISOFromInstance($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $inst . "/iso/detach");
    }

    /**
     * attachPrivateNetworkToInstance
     * Attaches a private network to an instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function attachPrivateNetworkToInstance($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (!isset($oa['network_id']) || !in_array($oa['network_id'], $this->api->privateNetworks()->ids)) {
            print "Network ID Not Found";
            exit;
        }
        $url = $this->api::INSTANCES_URL . "/" . $oa['id'] . "/private-networks/attach";
        $ba['network_id'] = $oa['network_id'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * detachPrivateNetworkFromInstance
     * Detaches a private network from an instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function detachPrivateNetworkFromInstance($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (!isset($oa['network_id']) || !in_array($oa['network_id'], $this->api->privateNetworks()->ids)) {
            print "Network ID Not Found";
            exit;
        }
        $url = $this->api::INSTANCES_URL . "/" . $oa['id'] . "/private-networks/detach";
        $ba['network_id'] = $oa['network_id'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * setInstanceBackupSchedule
     * Sets an Instance's Backup Schedule
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function setInstanceBackupSchedule($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (!isset($oa['type'])) {
            print "Backup Type Missing";
            exit;
        }
        if ($oa['type'] == "daily") {
            if (!isset($oa['hour']) || !is_numeric($oa['hour']) || $oa['hour'] > 24 || $oa['hour'] < 0) {
                print "Hour is invalid";
                exit;
            }
            $ba['type'] = $oa['type'];
            $ba['hour'] = $oa['hour'];
        } elseif ($oa['type'] == "weekly") {
            if (!isset($oa['hour']) || !is_numeric($oa['hour']) || $oa['hour'] > 24 || $oa['hour'] < 0) {
                print "Hour is invalid";
                exit;
            }
            if (!isset($oa['dow']) || !is_numeric($oa['dow']) || $oa['dow'] > 7 || $oa['dow'] < 0) {
                print "Day of Week (dow) is invalid";
                exit;
            }
            $ba['type'] = $oa['type'];
            $ba['hour'] = $oa['hour'];
            $ba['dow'] = $oa['dow'];
        } elseif ($oa['type'] == "monthly") {
            if (!isset($oa['hour']) || !is_numeric($oa['hour']) || $oa['hour'] > 24 || $oa['hour'] < 0) {
                print "Hour is invalid";
                exit;
            }
            if (!isset($oa['dom']) || !is_numeric($oa['dom']) || $oa['dom'] > 28 || $oa['dom'] < 1) {
                print "Day of Month (dom) is invalid";
                exit;
            }
            $ba['type'] = $oa['type'];
            $ba['hour'] = $oa['hour'];
            $ba['dom'] = $oa['dom'];
        } elseif ($oa['type'] == "daily_alt_even" || $oa['type'] == "daily_alt_odd") {
            if (!isset($oa['hour']) || !is_numeric($oa['hour']) || $oa['hour'] > 24 || $oa['hour'] < 0) {
                print "Hour is invalid";
                exit;
            }
            $ba['type'] = $oa['type'];
            $ba['hour'] = $oa['hour'];
        } else {
            print "Type is invalid";
            exit;
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/backup-schedule", $body);
    }

    /**
     * getInstanceBackupSchedule
     * Gets Instance Backup Schedule
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function getInstanceBackupSchedule($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/backup-schedule");
    }

    /**
     * restoreInstance
     * Restores an Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function restoreInstance($oa)
    {
        $hasOS = false;
        $this->checkInstanceId($oa['id']);
        if (!$hasOS && isset($oa['snapshot_id']) && in_array($oa['snapshot_id'], $this->api->snapshots()->ids)) {
            $hasOS = true;
            $ba['snapshot_id'] = $oa['snapshot_id'];
        }
        if (!$hasOS && isset($oa['backup_id']) && in_array($oa['backup_id'], $this->api->backup()->ids)) {
            $hasOS = true;
            $ba['backup_id'] = $oa['backup_id'];
        }
        if (!$hasOS) {
            print "A Valid OS (snapshot_id or backup_id) is missing";
            exit;
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/restore", $body);
    }

    /**
     * listInstanceIPv4Information
     * Lists IPv4 Information
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function listInstanceIPv4Information($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/ipv4");
    }

    /**
     * createIPv4
     * Creates IPv4 Address
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function createIPv4($oa)
    {
        $hasOS = false;
        $this->checkInstanceId($oa['id']);
        if (isset($oa['reboot']) && $oa['reboot'] == true) {
            $ba['reboot'] = true;
        } else {
            $ba['reboot'] = false;
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/ipv4", $body);
    }

    /**
     * getInstanceIPv6Information
     * Gets Information about the IPv6 data from the instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function getInstanceIPv6Information($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/ipv6");
    }

    /**
     * createInstanceReverseIPv6
     * Creates a reverse IPv6 record
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function createInstanceReverseIPv6($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (!isset($oa['ip']) || !filter_var($oa['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            print "IP Address missing or invalid";
            exit;
        }
        if (!isset($oa['reverse']) || !filter_var($oa['reverse'], FILTER_VALIDATE_DOMAIN)) {
            print "Reverse missing or it's not a valid domain";
            exit;
        }
        $ba['ip'] = $oa['ip'];
        $ba['reverse'] = $oa['reverse'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/ipv6/reverse", $body);
    }

    /**
     * listInstanceIPv6Reverse
     * Lists Reverse IPv6 Record
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function listInstanceIPv6Reverse($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/ipv6/reverse");
    }

    /**
     * createInstanceReverseIPv4
     * Creates a Reverse IPv4 Record
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function createInstanceReverseIPv4($oa)
    {
        $this->checkInstanceId($oa['id']);
        if (!isset($oa['ip']) || !filter_var($oa['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            print "IP Address missing or invalid";
            exit;
        }
        if (!isset($oa['reverse']) || !filter_var($oa['reverse'], FILTER_VALIDATE_DOMAIN)) {
            print "Reverse missing or it's not a valid domain";
            exit;
        }
        $ba['ip'] = $oa['ip'];
        $ba['reverse'] = $oa['reverse'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $oa['id'] . "/ipv4/reverse", $body);
    }

    /**
     * getInstanceUserData
     * Lists Instance User Data
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function getInstanceUserData($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/user-data");
    }


    /**
     * haltInstance
     * Halts an Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function haltInstance($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('POST', $this->api::INSTANCES_URL . "/" . $inst . "/halt");
    }

    /**
     * setDefaultReverseDNSEntry
     * Sets Default Reverse DNS Entry
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function setDefaultReverseDNSEntry($oa)
    {
        if (!in_array($oa['id'], $this->ids)) {
            print "Instance ID Not Found";
            exit;
        }
        if (!isset($oa['ip']) || !filter_var($oa['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            print "IP Address missing or invalid";
            exit;
        }
        $ba['ip'] = $oa['ip'];
        $url = $this->api::INSTANCES_URL . "/" . $oa['id'] . "/ipv4/reverse/default";
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * deleteIPv4Address
     * Deletes IPv4 Address
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function deleteIPv4Address($oa)
    {
        if (!in_array($oa['id'], $this->ids)) {
            print "Instance ID Not Found";
            exit;
        }
        if (!isset($oa['ip']) || !filter_var($oa['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            print "IP Address missing or invalid";
            exit;
        }
        $url = $this->api::INSTANCES_URL . "/" . $oa['id'] . "/ipv4/" . $oa['ip'];
        return $this->api->makeAPICall('DELETE', $url);
    }

    /**
     * deleteInstanceReverseIPv6
     * Deletes Instance Reverse IPv6 Information
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function deleteInstanceReverseIPv6($oa)
    {
        if (!in_array($oa['id'], $this->ids)) {
            print "Instance ID Not Found";
            exit;
        }
        if (!isset($oa['ip']) || !filter_var($oa['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            print "IP Address missing or invalid";
            exit;
        }
        $url = $this->api::INSTANCES_URL . "/" . $oa['id'] . "/ipv6/reverse/" . $oa['ip'];
        return $this->api->makeAPICall('DELETE', $url);
    }

    /**
     * getAvailableInstanceUpgrades
     * Gets Upgrade Information about an Instance
     *
     * @param $oa array
     *
     * @return void
     *
     */
    public function getAvailableInstanceUpgrades($inst)
    {
        $this->checkInstanceId($inst);
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $inst . "/upgrades");
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
     * getIds
     * Returns Instance IDs as an array
     *
     *
     * @return array
     *
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * getInstance
     * Get Instance Information
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function getInstance($id)
    {
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL . "/" . $id);
    }

    /**
     * deleteInstance
     * Delete's an Instance
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function deleteInstance($id)
    {
        return $this->api->makeAPICall('DELETE', $this->api::INSTANCES_URL . "/" . $id);
    }

    /**
     * checkInstanceId
     * Checks's if an Instance ID is valid or not
     *
     * @var string $id
     *
     * @return bool
     *
     */
    public function checkInstanceId($id)
    {
        if (in_array($id, $this->ids)) {
            return true;
        } else {
            print "Instance Not Found";
            exit;
        }
    }
}
