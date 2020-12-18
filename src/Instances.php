<?php

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
     * loadInstances
     * Loads Instances in to Array
     *
     *
     * @return void
     *
     */
    public function loadInstances()
    {
        $data = json_decode($this->listInstances(), true);
        foreach ($data['instances'] as $line) {
            $this->ids[] = $line['id'];
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
}
