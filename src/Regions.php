<?php

namespace dutchie027\Vultr;

class Regions
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array containing Region IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Array containing Cities
     *
     * @var array
     */
    public $cities = [];

    /**
     * Array containing Countries
     *
     * @var array
     */
    public $countries = [];

    /**
     * Array containing Continents
     *
     * @var array
     */
    public $continents = [];

    /**
     * Array containing Names
     *
     * @var array
     */
    public $names = [];

    /**
     * Array containing block IDs
     *
     * @var array
     */
    public $block_ids = [];

    /**
     * Array containing DDoS IDs
     *
     * @var array
     */
    public $ddos_ids = [];

    /**
     * Key to search for when looking for block storage
     *
     * @var string
     */
    public $bs_flag = "block_storage";

    /**
     * Key to look for when looking for DDOS Protection
     *
     * @var string
     */
    public $ddos_flag = "ddos_protection";

    /**
     * __construct
     * Main Construct - Loads Regions in to arrays and creates reference
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
        $this->loadRegionArrays();
    }

    /**
     * listRegions
     * Returns a list of Regions in the default JSON Array Format
     *
     *
     * @return string JSON body
     *
     */
    public function listRegions()
    {
        return $this->api->makeAPICall('GET', $this->api::REGIONS_URL);
    }

    /**
     * loadRegionArrays
     * Loads arrays with region information
     *
     *
     * @return void
     *
     */
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

    /**
     * listCities
     * Prints Cities to stdout
     *
     *
     * @return void
     *
     */
    public function listCities()
    {
        foreach ($this->cities as $city) {
            print $city . PHP_EOL;
        }
    }

    /**
     * listIds
     * Prints Ususble IDs to stdout
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
     * listCountries
     * Prints Countries to stdout
     *
     *
     * @return void
     *
     */
    public function listCountries()
    {
        foreach ($this->countries as $country) {
            print $country . PHP_EOL;
        }
    }

    /**
     * listContinents
     * Prints Continents to stdout
     *
     *
     * @return void
     *
     */
    public function listContinents()
    {
        foreach ($this->continents as $continent) {
            print $continent . PHP_EOL;
        }
    }

    /**
     * listNames
     * Prints Names to stdout
     *
     *
     * @return void
     *
     */
    public function listNames()
    {
        foreach ($this->names as $name) {
            print $name . PHP_EOL;
        }
    }

    /**
     * getIds
     * Returns the array of ususble IDs
     *
     *
     * @return array
     *
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * getDDOSIds
     * Returns array of IDs that allow DDOS
     *
     *
     * @return array
     *
     */
    public function getDDOSIds()
    {
        return $this->ddos_ids;
    }

    /**
     * getBlockIds
     * Returns array of IDs that allow block storage
     *
     *
     * @return array
     *
     */
    public function getBlockIds()
    {
        return $this->block_ids;
    }
}
