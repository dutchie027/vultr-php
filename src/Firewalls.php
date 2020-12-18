<?php

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
     * Gets information on a backup
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
}
