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
    public $metal_plan = [];

    /**
     * Count of Total Metal Plans
     *
     * @var int
     */
    protected $total_metal_plans;

    /**
     * __construct
     * Takes reference from \API
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
     */
    public function listPlans(): string
    {
        return $this->api->makeAPICall('GET', $this->api::PLANS_URL);
    }

    /**
     * listMetalPlans
     * List Bare Metal Plans
     */
    public function listMetalPlans(): string
    {
        return $this->api->makeAPICall('GET', $this->api::METAL_PLANS_URL);
    }

    /**
     * loadPlans
     * Loads Plan Information in to arrays
     */
    public function loadPlans(): void
    {
        $pa = json_decode($this->listPlans(), true);

        foreach ($pa['plans'] as $plan) {
            $id = $plan['id'];
            $this->ids[] = $id;
            $this->plan[$id] = $plan;
        }
        $this->total_plans = $pa['meta']['total'];
    }

    /**
     * loadMetalPlans
     * Loads Metal Plan Information in to arrays
     */
    public function loadMetalPlans(): void
    {
        $pa = json_decode($this->listMetalPlans(), true);

        foreach ($pa['plans_metal'] as $plan) {
            $id = $plan['id'];
            $this->metal_ids[] = $id;
            $this->metal_plan[$id] = $plan;
        }
        $this->total_metal_plans = $pa['meta']['total'];
    }

    /**
     * getNumberOfPlans
     * Returns total number of Plans
     */
    public function getNumberOfPlans(): int
    {
        return $this->total_plans;
    }

    /**
     * getAllPlans
     * Returns Bare Metal and normal plans combined together.
     */
    public function getAllPlans(): array
    {
        return array_merge($this->plan, $this->metal_plan);
    }
}
