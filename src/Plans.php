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

class Plans
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of All Plan IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Array of Plan Information
     *
     * @var array
     */
    public $plan = [];

    /**
     * Count of Total Plans
     *
     * @var int
     */
    protected $total_plans;

    /**
     * Array of All Metal Plan IDs
     *
     * @var array
     */
    protected $metal_ids = [];

    /**
     * Array of Metal Plan Information
     *
     * @var array
     */
    protected $metal_plan = [];

    /**
     * Count of Total Metal Plans
     *
     * @var int
     */
    protected $total_metal_plans;

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
        $this->loadPlans();
        $this->loadMetalPlans();
    }

    /**
     * listPlans
     * List All Plans
     *
     *
     * @return string
     *
     */
    public function listPlans()
    {
        return $this->api->makeAPICall('GET', $this->api::PLANS_URL);
    }

    /**
     * listMetalPlans
     * List Bare Metal Plans
     *
     *
     * @return string
     *
     */
    public function listMetalPlans()
    {
        return $this->api->makeAPICall('GET', $this->api::METAL_PLANS_URL);
    }

    /**
     * loadPlans
     * Loads Plan Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadPlans()
    {
        $pa = json_decode($this->listPlans(), true);
        foreach ($pa['plans'] as $plan) {
            $id = $plan['id'];
            $this->ids[] = $id;
            $this->plan[$id]['type'] = $plan['type'];
            $this->plan[$id]['monthly_cost'] = $plan['monthly_cost'];
            $this->plan[$id]['bandwidth'] = $plan['bandwidth'];
            $this->plan[$id]['disk'] = $plan['disk'];
            $this->plan[$id]['ram'] = $plan['ram'];
            $this->plan[$id]['vcpu_count'] = $plan['vcpu_count'];
            $this->plan[$id]['locations'] = $plan['locations'];
        }
        $this->total_plans = $pa['meta']['total'];
    }

    /**
     * loadMetalPlans
     * Loads Metal Plan Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadMetalPlans()
    {
        $pa = json_decode($this->listMetalPlans(), true);
        foreach ($pa['plans_metal'] as $plan) {
            $id = $plan['id'];
            $this->metal_ids[] = $id;
            $this->metal_plan[$id]['cpu_count'] = $plan['cpu_count'];
            $this->metal_plan[$id]['cpu_threads'] = $plan['cpu_threads'];
            $this->metal_plan[$id]['cpu_model'] = $plan['cpu_model'];
            $this->metal_plan[$id]['disk'] = $plan['disk'];
            $this->metal_plan[$id]['ram'] = $plan['ram'];
            $this->metal_plan[$id]['monthly_cost'] = $plan['monthly_cost'];
            $this->metal_plan[$id]['bandwidth'] = $plan['bandwidth'];
            $this->metal_plan[$id]['type'] = $plan['type'];
            $this->metal_plan[$id]['locations'] = $plan['locations'];
        }
        $this->total_metal_plans = $pa['meta']['total'];
    }

    /**
     * getNumberOfPlans
     * Returns total number of Plans
     *
     *
     * @return int
     *
     */
    public function getNumberOfPlans()
    {
        return $this->total_plans;
    }
}
