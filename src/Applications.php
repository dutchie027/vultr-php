<?php

namespace dutchie027\Vultr;

class Applications
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of Application IDs
     *
     * @var array
     */
    protected $ids = [];

    /**
     * Array of Names
     *
     * @var array
     */
    protected $names = [];

    /**
     * Array of Short Names
     *
     * @var array
     */
    protected $short_names = [];

    /**
     * Array of Deployable Names
     *
     * @var array
     */
    protected $deploy_names = [];

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
        $this->loadApplications();
    }

    /**
     * List Applicaitons
     * Lists available applicaitons
     *
     *
     * @return string
     *
     */
    public function listApplications()
    {
        return $this->api->makeAPICall('GET', $this->api::APPLICATIONS_URL);
    }

    /**
     * Load Applicaitons
     * Loads Applications in to arrays
     *
     *
     * @return void
     *
     */
    public function loadApplications()
    {
        $apps = json_decode($this->listApplications(), true);
        foreach ($apps['applications'] as $ap) {
            $this->ids[] = $ap['id'];
            $this->names[] = $ap['name'];
            $this->short_names[] = $ap['short_name'];
            $this->deploy_names[$ap['id']] = $ap['deploy_name'];
        }
    }

    /**
     * printNames
     * Prints Names and IDs to stdout
     *
     *
     * @return void
     *
     */
    public function printNames()
    {
        foreach ($this->ids as $id) {
            print $this->deploy_names[$id] . " ($id)" . PHP_EOL;
        }
    }
}
