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

class Applications
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of Application IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Total Count of Applications
     *
     * @var int
     */
    protected $app_count;

    /**
     * Array of Names
     *
     * @var array<string>
     */
    protected $names = [];

    /**
     * Array of Short Names
     *
     * @var array<string>
     */
    protected $short_names = [];

    /**
     * Array of Deployable Names
     *
     * @var array<string>
     */
    protected $deploy_names = [];

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadApplications();
    }

    /**
     * List Applicaitons
     * Lists available applicaitons
     */
    public function listApplications(): string
    {
        return $this->api->makeAPICall('GET', $this->api::APPLICATIONS_URL);
    }

    /**
     * Load Applicaitons
     * Loads Applications in to arrays
     */
    public function loadApplications(): void
    {
        $apps = json_decode($this->listApplications(), true);

        foreach ($apps['applications'] as $ap) {
            $this->ids[] = $ap['id'];
            $this->names[] = $ap['name'];
            $this->short_names[] = $ap['short_name'];
            $this->deploy_names[$ap['id']] = $ap['deploy_name'];
        }
        $this->app_count = $apps['meta']['total'];
    }

    /**
     * getNumberOfApplications
     * Returns total number of applications
     */
    public function getNumberOfApplications(): int
    {
        return $this->app_count;
    }
}
