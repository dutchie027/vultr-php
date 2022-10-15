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
        'v4',
        'v6',
    ];

    /**
     * Array of Valid Protocols When Creating A
     * New Firewall Rule
     *
     * @var array
     */
    private $valid_protos = [
        'ICMP',
        'TCP',
        'UDP',
        'GRE',
        'ESP',
        'AH',
    ];

    /**
     * Default label
     *
     * @var string
     */
    private $d_note = '';

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
    protected $d_description = '';

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
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadFirewallArrays();
    }

    /**
     * listFirewallGroups
     * Gets account info
     */
    public function listFirewallGroups(): string
    {
        return $this->api->makeAPICall('GET', $this->api::FIREWALLS_URL);
    }

    public function loadFirewallArrays(): void
    {
        $fw = json_decode($this->listFirewallGroups(), true);

        foreach ($fw['firewall_groups'] as $fire) {
            $id = $fire['id'];
            $this->ids[] = $fire['id'];
            $this->fwrga[$id] = $fire;
        }
        $this->total_rule_groups = $fw['meta']['total'];
    }

    /**
     * getFirewallGroup
     * Gets information on a Firewall Group
     */
    public function getFirewallGroup(string $id): string
    {
        if (in_array($id, $this->ids, true)) {
            return $this->api->makeAPICall('GET', $this->api::FIREWALLS_URL . '/' . $id);
        }

        throw new InvalidParameterException("That Firewall Group ID doesn't exist");
    }

    /**
     * listFirewallRules
     * Lists Firewall Rules
     */
    public function listFirewallRules(string $id): string
    {
        if (in_array($id, $this->ids, true)) {
            return $this->api->makeAPICall('GET', $this->api::FIREWALLS_URL . '/' . $id . '/rules');
        }

        throw new InvalidParameterException("That Firewall Group ID doesn't exist");
    }

    /**
     * createFirewallGroup
     * Creates a New Firewall Group
     */
    public function createFirewallGroup(string $name): string
    {
        if (strlen($name) < 4) {
            throw new InvalidParameterException('Name needs to be a minimum of 4 characters');
        }
        $ba['description'] = $name;

        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $this->api::FIREWALLS_URL, $body);
    }

    /**
     * createFirewallRule
     * Creates a New Rule
     */
    public function createFirewallRule(array $fa): string
    {
        $ba['notes'] = $this->d_note;

        if (isset($fa['id']) && in_array($fa['id'], $this->ids, true)) {
            $url = $this->api::FIREWALLS_URL . '/' . $fa['id'] . '/rules';
        } else {
            throw new InvalidParameterException("Firewall Group ID doesn't exist or noit defined");
        }

        if (isset($fa['ip_type']) && in_array($fa['ip_type'], $this->valid_ip_types, true)) {
            $ba['ip_type'] = $fa['ip_type'];
        } else {
            throw new InvalidParameterException("Invalid IP Type. Must be 'v4' or 'v6'");
        }

        if (isset($fa['protocol']) && in_array(strtoupper($fa['protocol']), $this->valid_protos, true)) {
            $ba['protocol'] = strtoupper($fa['protocol']);
        } else {
            throw new InvalidParameterException('Invalid protocol. Must be one of these: ' . $this->valid_protos);
        }

        if (isset($fa['subnet']) && filter_var($fa['subnet'], FILTER_VALIDATE_IP)) {
            $ba['subnet'] = $fa['subnet'];
        } else {
            throw new InvalidParameterException('Invalid IP address for the subnet key');
        }

        if (isset($fa['port'])) {
            $prm = "/^(\d+)([\:\-]?)(\d+)?$/";

            if (preg_match($prm, $fa['port'], $matches)) {
                if (count($matches) === 3) {
                    if ($matches[1] < 0 || $matches[1] > 65535) {
                        throw new InvalidParameterException('Invalid Port - Must be between 0 and 65535');
                    }
                    $port = $matches[1];

                    if (preg_match("/[\-\:]/", $matches[2])) {
                        if ($port < 65535) {
                            $port .= ':65535';
                        }
                    }
                } elseif (count($matches) === 4) {
                    $port1 = $matches[1];
                    $port2 = $matches[3];

                    if ($port1 < 0 || $port1 > 65535) {
                        throw new InvalidParameterException('Port values must be between 0 and 65535');
                    }

                    if ($port2 < 0 || $port2 > 65535) {
                        throw new InvalidParameterException('Port values must be between 0 and 65535');
                    }

                    if ($port1 > $port2) {
                        throw new InvalidParameterException("The first port can't be lesser than the second port");
                    }

                    if ($port1 == $port2) {
                        $port = $port1;
                    } else {
                        $port = $port1 . ':' . $port2;
                    }
                    $ba['port'] = $port;
                } else {
                    throw new InvalidParameterException('Something went wrong');
                }
            } else {
                throw new InvalidParameterException('Port Value Invalid');
            }
        } else {
            throw new InvalidParameterException('Port not set');
        }

        if (isset($fa['subnet_size']) && is_numeric($fa['subnet_size'])) {
            if ($fa['subnet_size'] < 0 || $fa['subnet_size'] > 32) {
                throw new InvalidParameterException('Subnet size is must between 0 and 32');
            }
            $ba['subnet_size'] = $fa['subnet_size'];
        } else {
            throw new InvalidParameterException('Subnet size is not set or is not numeric');
        }
        (isset($fa['notes'])) ? $ba['notes'] = $fa['notes'] : null;
        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * getNumberOfFirewallRules
     * Returns total number of Firewall Rule Groups
     */
    public function getNumberOfFirewallRules(): int
    {
        return $this->total_rule_groups;
    }

    /**
     * getNumberOfRules
     * Returns total number of Rules associated with a specific
     * Firewall Group ID
     */
    public function getNumberOfRules(string $id): int
    {
        if (in_array($id, $this->ids, true)) {
            return $this->fwrga[$id]['rule_count'];
        }

        throw new InvalidParameterException("That Firewall Group ID doesn't exist");
    }

    /**
     * getRuleName
     * Returns Rule Name as assocaited with a
     * Firewall Group ID
     */
    public function getRuleName(string $id): int
    {
        if (in_array($id, $this->ids, true)) {
            return $this->fwrga[$id]['desc'];
        }

        throw new InvalidParameterException("That Firewall Group ID doesn't exist");
    }

    /**
     * getInstanceCount
     * Returns Count of instances associated with given
     * Firewall Group ID
     */
    public function getInstanceCount(string $id): int
    {
        if (in_array($id, $this->ids, true)) {
            return $this->fwrga[$id]['instance_count'];
        }

        throw new InvalidParameterException("That Firewall Group ID doesn't exist");
    }

    /**
     * updateFirewallGroup
     * Updates label of Firewall Group
     */
    public function updateFirewallGroup(array $options): string
    {
        if (in_array($options['group_id'], $this->ids, true)) {
            $url = $this->api::FIREWALLS_URL . '/' . $options['group_id'];
        } else {
            throw new InvalidParameterException("That Firewall Group ID isn't associated with your account");
        }
        $ba['description'] = $this->d_description;
        (isset($options['description'])) ? $ba['description'] = $options['description'] : null;
        $body = json_encode($ba);

        return $this->api->makeAPICall('PUT', $url, $body);
    }

    /**
     * deleteFirewallGroup
     * Deletes a Firewall Group
     */
    public function deleteFirewallGroup(string $id): string
    {
        if (in_array($id, $this->ids, true)) {
            $url = $this->api::FIREWALLS_URL . '/' . $id;
        } else {
            throw new InvalidParameterException("That Firewall Group ID isn't associated with your account");
        }

        return $this->api->makeAPICall('DELETE', $url);
    }
}
