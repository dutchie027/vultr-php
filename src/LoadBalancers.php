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

class LoadBalancers
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
    public $loadBalancer = [];

    /**
     * Count of Total IPs
     *
     * @var int
     */
    protected $total_load_balancers;

    /**
     * Frontend Protocols
     *
     * @var array
     */
    private $frontend_proto = [
        'HTTP',
        'HTTPS',
        'TCP',
    ];

    /**
     * Backend Protocols
     *
     * @var array
     */
    private $backend_proto = [
        'HTTP',
        'HTTPS',
        'TCP',
    ];

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
        $this->loadLoadBalancers();
    }

    /**
     * listLoadBalancers
     * List all Reserved IPs in your account
     *
     * @see https://www.vultr.com/api/v2/#operation/list-load-balancers
     */
    public function listLoadBalancers(): string
    {
        return $this->api->makeAPICall('GET', $this->api::LOAD_BALANCERS_URL);
    }

    /**
     * loadReservedIPs
     * Loads Reserved IP Information in to arrays
     */
    public function loadLoadBalancers(): void
    {
        $lba = json_decode($this->listLoadBalancers(), true);

        foreach ($lba['load_balancers'] as $key) {
            $id = $key['id'];
            $this->ids[] = $id;
            $this->loadBalancer[$id] = $key;
        }
        $this->total_load_balancers = $lba['meta']['total'];
    }

    /**
     * getLoadBalancer
     * Get information for a Load Balancer.
     *
     * @see https://www.vultr.com/api/v2/#operation/get-load-balancer
     */
    public function getLoadBalancer(string $id): string
    {
        return $this->api->makeAPICall('GET', $this->api::LOAD_BALANCERS_URL . '/' . $id);
    }

    /**
     * deleteLoadBalancer
     * Delete a Load Balancer.
     *
     * @see https://www.vultr.com/api/v2/#operation/delete-load-balancer
     */
    public function deleteLoadBalancer(string $id): string
    {
        return $this->api->makeAPICall('DELETE', $this->api::LOAD_BALANCERS_URL . '/' . $id);
    }

    /**
     * listForwardingRules
     * List the fowarding rules for a Load Balancer.
     *
     * @see https://www.vultr.com/api/v2/#operation/list-load-balancer-forwarding-rules
     */
    public function listForwardingRules(string $id): string
    {
        $url = $this->api::LOAD_BALANCERS_URL . '/' . $id . '/forwarding-rules';

        return $this->api->makeAPICall('GET', $url);
    }

    /**
     * createForwardingRule
     * List the fowarding rules for a Load Balancer.
     *
     * @see https://www.vultr.com/api/v2/#operation/create-load-balancer-forwarding-rules
     */
    public function createForwardingRule(array $oa): string
    {
        $this->checkLoadBalancer($oa['load-balancer-id']);

        if (!isset($oa['frontend_protocol']) || !in_array($oa['frontend_protocol'], $this->frontend_proto, true)) {
            throw new InvalidParameterException('Front End Protocol Missing or Invalid');
        }

        if (!isset($oa['backend_protocol']) || !in_array($oa['backend_protocol'], $this->backend_proto, true)) {
            throw new InvalidParameterException('Front End Protocol Missing or Invalid');
        }

        if (!isset($oa['frontend_port']) || $oa['frontend_port'] > 65535 || $oa['frontend_port'] < 1) {
            throw new InvalidParameterException('Frontend port invalid');
        }

        if (!isset($oa['backend_port']) || $oa['backend_port'] > 65535 || $oa['backend_port'] < 1) {
            throw new InvalidParameterException('Backend port invalid');
        }
        $ba['frontend_protocol'] = $oa['frontend_protocol'];
        $ba['backend_protocol'] = $oa['backend_protocol'];
        $ba['frontend_port'] = $oa['frontend_port'];
        $ba['backend_port'] = $oa['backend_port'];
        $body = json_encode($ba);
        $url = $this->api::LOAD_BALANCERS_URL . '/' . $oa['load-balancer-id'] . '/forwarding-rules';

        return $this->api->makeAPICall('POST', $url, $body);
    }

    // TODO: Add more check logic to ensure the rule id exists first
    /**
     * getForwardingRule
     * List the fowarding rules for a Load Balancer.
     *
     * @see https://www.vultr.com/api/v2/#operation/get-load-balancer-forwarding-rule
     */
    public function getForwardingRule(array $oa): string
    {
        $this->checkLoadBalancer($oa['load-balancer-id']);
        // TODO: Check to ensure the rule here is right
        $lbid = $oa['load-balancer-id'];
        $rid = $oa['forwarding-rule-id'];
        $url = $this->api::LOAD_BALANCERS_URL . '/' . $lbid . '/forwarding-rules/' . $rid;

        return $this->api->makeAPICall('GET', $url);
    }

    // TODO: Add more check logic to ensure the rule id exists first
    /**
     * deleteForwardingRule
     * List the fowarding rules for a Load Balancer.
     *
     * @see https://www.vultr.com/api/v2/#operation/delete-load-balancer-forwarding-rule
     */
    public function deleteForwardingRule(array $oa): string
    {
        $this->checkLoadBalancer($oa['load-balancer-id']);
        // TODO: Check to ensure the rule here is right
        $lbid = $oa['load-balancer-id'];
        $rid = $oa['forwarding-rule-id'];
        $url = $this->api::LOAD_BALANCERS_URL . '/' . $lbid . '/forwarding-rules/' . $rid;

        return $this->api->makeAPICall('DELETE', $url);
    }

    /**
     * checkLoadBalancer
     * Checks's if a Load Balancer ID is valid or not
     */
    public function checkLoadBalancer(string $id): bool
    {
        if (in_array($id, $this->ids, true)) {
            return true;
        }

        throw new InvalidParameterException('Load Balancer ID Not Found');
    }

    // TODO: Stubbed Out but Not finished
    /**
     * createLoadBalancer
     * Create a new Load Balancer in a particular region.
     *
     * @see https://www.vultr.com/api/v2/#operation/create-load-balancer
     */
    public function createLoadBalancer(array $oa): string
    {
    }

    // TODO: Stubbed Out but Not finished
    /**
     * updateLoadBalancer
     * Create a new Load Balancer in a particular region.
     *
     * @var string
     *
     * @return bool
     *
     * @see https://www.vultr.com/api/v2/#operation/update-load-balancer
     */
    public function updateLoadBalancer(array $oa): string
    {
    }
}
