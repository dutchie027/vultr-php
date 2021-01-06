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

class Firewalls
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of Valid IP Types When Creating A
     * New Firewall Rule
     *
     * @var array
     */
    private $valid_ip_types = [
        "v4",
        "v6"
    ];

    /**
     * Array of Valid Protocols When Creating A
     * New Firewall Rule
     *
     * @var array
     */
    private $valid_protos = [
        "ICMP",
        "TCP",
        "UDP",
        "GRE",
        "ESP",
        "AH",
    ];

    /**
     * Default label
     *
     * @var string
     */
    private $d_note = "";

    /**
     * Array of Firewall Group IDs
     *
     * @var array
     */
    protected $ids = [];

    /**
     * Default Firewall Group Description
     *
     * @var string
     */
    protected $d_description = "";

    /**
     * Total Number of Firewall Groups
     *
     * @var int
     */
    protected $total_rule_groups;

    /**
     * Firewall Rule Group Array
     *
     * @var array
     */
    protected $fwrga;

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
        $this->loadFirewallArrays();
    }

    /**
     * listFirewallGroups
     * Gets account info
     *
     *
     * @return string
     *
     */
    public function listFirewallGroups()
    {
        return $this->api->makeAPICall('GET', $this->api::FIREWALLS_URL);
    }

    public function loadFirewallArrays()
    {
        $fw = json_decode($this->listFirewallGroups(), true);
        foreach ($fw['firewall_groups'] as $fire) {
            $id = $fire['id'];
            $this->ids[] = $fire['id'];
            $this->fwrga[$id]['desc'] = $fire['description'];
            $this->fwrga[$id]['instance_count'] = $fire['instance_count'];
            $this->fwrga[$id]['rule_count'] = $fire['rule_count'];
            $this->fwrga[$id]['max_rule_count'] = $fire['max_rule_count'];
        }
        $this->total_rule_groups = $fw['meta']['total'];
    }

    /**
     * getFirewallGroup
     * Gets information on a Firewall Group
     *
     * @param string $id
     *
     * @return string
     *
     */
    public function getFirewallGroup($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->api->makeAPICall('GET', $this->api::FIREWALLS_URL . "/" . $id);
        } else {
            print "That Firewall Group ID doesn't exist";
            exit;
        }
    }

    /**
     * listFirewallRules
     * Lists Firewall Rules
     *
     * @param string $id
     *
     * @return string
     *
     */
    public function listFirewallRules($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->api->makeAPICall('GET', $this->api::FIREWALLS_URL . "/" . $id . "/rules");
        } else {
            print "That Firewall Group ID doesn't exist";
            exit;
        }
    }

    /**
     * createFirewallGroup
     * Creates a New Firewall Group
     *
     * @param string $name
     *
     * @return string
     *
     */
    public function createFirewallGroup($name)
    {
        if (strlen($name) < 4) {
            print "Name needs to be a minimum of 4 characters";
            exit;
        } else {
            $ba['description'] = $name;
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::FIREWALLS_URL, $body);
    }

    /**
     * createFirewallRule
     * Creates a New Rule
     *
     * @param array $fa
     *
     * @return string
     *
     */
    public function createFirewallRule($fa)
    {
        $ba['notes'] = $this->d_note;
        if (isset($fa['id']) && in_array($fa['id'], $this->ids)) {
            $url = $this->api::FIREWALLS_URL . "/" . $fa['id'] . "/rules";
        } else {
            print "Firewall Group ID doesn't exist or noit defined";
            exit;
        }
        if (isset($fa['ip_type']) && in_array($fa['ip_type'], $this->valid_ip_types)) {
            $ba['ip_type'] = $fa['ip_type'];
        } else {
            print "Invalid IP Type. Must be 'v4' or 'v6'";
            exit;
        }
        if (isset($fa['protocol']) && in_array(strtoupper($fa['protocol']), $this->valid_protos)) {
            $ba['protocol'] = strtoupper($fa['protocol']);
        } else {
            print "Invalid protocol. Must be one of these:" . PHP_EOL;
            print_r($this->valid_protos);
            exit;
        }
        if (isset($fa['subnet']) && filter_var($fa['subnet'], FILTER_VALIDATE_IP)) {
            $ba['subnet'] = $fa['subnet'];
        } else {
            print "Invalid IP address for the subnet key";
            exit;
        }
        if (isset($fa['port'])) {
            $prm = "/^(\d+)([\:\-]?)(\d+)?$/";
            if (preg_match($prm, $fa['port'], $matches)) {
                if (count($matches) === 3) {
                    if ($matches[1] < 0 || $matches[1] > 65535) {
                        print "Invalid Port - Must be between 0 and 65535";
                        exit;
                    } else {
                        $port = $matches[1];
                    }
                    if (preg_match("/[\-\:]/", $matches[2])) {
                        if ($port < 65535) {
                            $port .= ":65535";
                        }
                    }
                } elseif (count($matches) === 4) {
                    $port1 = $matches[1];
                    $port2 = $matches[3];
                    if ($port1 < 0 || $port1 > 65535) {
                        print "Port values must be between 0 and 65535";
                        exit;
                    }
                    if ($port2 < 0 || $port2 > 65535) {
                        print "Port values must be between 0 and 65535";
                        exit;
                    }
                    if ($port1 > $port2) {
                        print "The first port can't be lesser than the second port";
                        exit;
                    }
                    if ($port1 == $port2) {
                        $port = $port1;
                    } else {
                        $port = $port1 . ":" . $port2;
                    }
                    $ba['port'] = $port;
                } else {
                    print "Something went wrong";
                    exit;
                }
            } else {
                print "Port Value Invalid";
                exit;
            }
        } else {
            print "Port not set";
            exit;
        }
        if (isset($fa['subnet_size']) && is_numeric($fa['subnet_size'])) {
            if ($fa['subnet_size'] < 0 || $fa['subnet_size'] > 32) {
                print "Subnet size is must between 0 and 32";
                exit;
            } else {
                $ba['subnet_size'] = $fa['subnet_size'];
            }
        } else {
            print "Subnet size is not set or is not numeric";
            exit;
        }
        (isset($fa['notes'])) ? $ba['notes'] = $fa['notes'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * getNumberOfFirewallRules
     * Returns total number of Firewall Rule Groups
     *
     *
     * @return int
     *
     */
    public function getNumberOfFirewallRules()
    {
        return $this->total_rule_groups;
    }

    /**
     * getNumberOfRules
     * Returns total number of Rules associated with a specific
     * Firewall Group ID
     *
     * @param string $id
     *
     * @return int
     *
     */
    public function getNumberOfRules($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->fwrga[$id]['rule_count'];
        } else {
            print "That Firewall Group ID doesn't exist";
            exit;
        }
    }

    /**
     * getRuleName
     * Returns Rule Name as assocaited with a
     * Firewall Group ID
     *
     * @param string $id
     *
     * @return int
     *
     */
    public function getRuleName($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->fwrga[$id]['desc'];
        } else {
            print "That Firewall Group ID doesn't exist";
            exit;
        }
    }

    /**
     * getInstanceCount
     * Returns Count of instances associated with given
     * Firewall Group ID
     *
     * @param string $id
     *
     * @return int
     *
     */
    public function getInstanceCount($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->fwrga[$id]['instance_count'];
        } else {
            print "That Firewall Group ID doesn't exist";
            exit;
        }
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
     * updateFirewallGroup
     * Updates label of Firewall Group
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function updateFirewallGroup($options)
    {
        if (in_array($options['group_id'], $this->ids)) {
            $url = $this->api::FIREWALLS_URL . "/" . $options['group_id'];
        } else {
            print "That Firewall Group ID isn't associated with your account";
            exit;
        }
        $ba['description'] = $this->d_description;
        (isset($options['description'])) ? $ba['description'] = $options['description'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('PUT', $url, $body);
    }

    /**
     * deleteFirewallGroup
     * Deletes a Firewall Group
     *
     * @param string $options
     *
     * @return string
     *
     */
    public function deleteFirewallGroup($id)
    {
        if (in_array($id, $this->ids)) {
            $url = $this->api::FIREWALLS_URL . "/" . $id;
        } else {
            print "That Firewall Group ID isn't associated with your account";
            exit;
        }
        return $this->api->makeAPICall('DELETE', $url);
    }
}
