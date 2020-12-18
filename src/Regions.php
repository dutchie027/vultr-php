<?php

namespace dutchie027\Vultr;

class Regions
{

    protected $api;

    public $ids = array();
    public $cities = array();
    public $countries = array();
    public $continents = array();
    public $names = array();
    public $block_ids = array();
    public $ddos_ids = array();
    public $bs_flag = "block_storage";
    public $ddos_flag = "ddos_protection";

    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadRegionArrays();
    }

    public function listRegions()
    {
        return $this->api->makeAPICall('GET', $this->api::REGIONS_URL);
    }

    public function loadRegionArrays()
    {
        $data = json_decode($this->listRegions(), true);
        foreach ($data['regions'] as $line) {
            $this->ids[] = $line['id'];
            $this->cities[] = $line['city'];
            $this->names[] = $line['city'] . " (" . $line['id'] . ")";
            (!in_array($line['country'], $this->countries)) ? $this->countries[] = $line['country'] : null ;
            (!in_array($line['continent'], $this->continents)) ? $this->continents[] = $line['continent'] : null ;
            (in_array($this->bs_flag, $line['options'])) ? $this->block_ids[] = $line['id'] : null ;
            (in_array($this->ddos_flag, $line['options'])) ? $this->ddos_ids[] = $line['id'] : null ;
        }
    }

    public function listCities()
    {
        foreach ($this->cities as $city) {
            print $city . PHP_EOL;
        }
    }

    public function listIds()
    {
        foreach ($this->ids as $id) {
            print $id . PHP_EOL;
        }
    }

    public function listCountries()
    {
        foreach ($this->countries as $country) {
            print $country . PHP_EOL;
        }
    }

    public function listContinents()
    {
        foreach ($this->continents as $continent) {
            print $continent . PHP_EOL;
        }
    }

    public function listNames()
    {
        foreach ($this->names as $name) {
            print $name . PHP_EOL;
        }
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function getDDOSIds()
    {
        return $this->ddos_ids;
    }

    public function getBlockIds()
    {
        return $this->block_ids;
    }
}
