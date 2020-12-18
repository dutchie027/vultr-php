<?php

namespace dutchie027\Vultr;

class Instances
{

    protected $api;

    public $ids = array();

    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadInstances();
    }

    public function listInstances()
    {
        return $this->api->makeAPICall('GET', $this->api::INSTANCES_URL);
    }

    public function loadInstances()
    {
        $data = json_decode($this->listInstances(), true);
        foreach ($data['instances'] as $line) {
            $this->ids[] = $line['id'];
        }
    }

    public function listIds()
    {
        foreach ($this->ids as $id) {
            print $id . PHP_EOL;
        }
    }

    public function getIds()
    {
        return $this->ids;
    }
}
