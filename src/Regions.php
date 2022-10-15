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

class Regions
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array containing Region IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Array containing Cities
     *
     * @var array<string>
     */
    public $cities = [];

    /**
     * Array containing Countries
     *
     * @var array<string>
     */
    public $countries = [];

    /**
     * Array containing Continents
     *
     * @var array<string>
     */
    public $continents = [];

    /**
     * Array containing Names
     *
     * @var array<string>
     */
    public $names = [];

    /**
     * Array containing block IDs
     *
     * @var array<int>
     */
    public $block_ids = [];

    /**
     * Array containing DDoS IDs
     *
     * @var array<int>
     */
    public $ddos_ids = [];

    /**
     * Key to search for when looking for block storage
     *
     * @var string
     */
    public $bs_flag = 'block_storage';

    /**
     * Key to look for when looking for DDOS Protection
     *
     * @var string
     */
    public $ddos_flag = 'ddos_protection';

    /**
     * Cached region data.
     *
     * @var array<mixed>
     */
    private $region_data = [];

    /**
     * __construct
     * Main Construct - Loads Regions in to arrays and creates reference
     * from main API class
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadRegionArrays();
    }

    /**
     * listRegions
     * Returns a list of Regions in the default JSON Array Format
     */
    public function listRegions(): string
    {
        return $this->api->makeAPICall('GET', $this->api::REGIONS_URL);
    }

    /**
     * getRegions
     * Gets the regions with associated data from API
     * @return array<mixed>
     */
    public function getRegions(): array
    {
        $build_data = [];

        foreach ($this->region_data['regions'] as $line) {
            $build_data[$line['id']] = $line;
        }

        return $build_data;
    }

    /**
     * loadRegionArrays
     * Loads arrays with region information
     */
    public function loadRegionArrays(): void
    {
        $this->region_data = $data = json_decode($this->listRegions(), true);

        foreach ($data['regions'] as $line) {
            $this->ids[] = $line['id'];
            $this->cities[] = $line['city'];
            $this->names[] = $line['city'] . ' (' . $line['id'] . ')';
            (!in_array($line['country'], $this->countries, true)) ? $this->countries[] = $line['country'] : null;
            (!in_array($line['continent'], $this->continents, true)) ? $this->continents[] = $line['continent'] : null;
            (in_array($this->bs_flag, $line['options'], true)) ? $this->block_ids[] = $line['id'] : null;
            (in_array($this->ddos_flag, $line['options'], true)) ? $this->ddos_ids[] = $line['id'] : null;
        }
    }

    /**
     * getIds
     * Returns the array of ususble IDs
     * @return array<int>
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * getDDOSIds
     * Returns array of IDs that allow DDOS
     * @return array<int>
     */
    public function getDDOSIds(): array
    {
        return $this->ddos_ids;
    }

    /**
     * getBlockIds
     * Returns array of IDs that allow block storage
     * @return array<int>
     */
    public function getBlockIds(): array
    {
        return $this->block_ids;
    }
}
