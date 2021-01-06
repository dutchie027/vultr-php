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

class OperatingSystems
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of All OS IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Total Count of Available OS'es
     *
     * @var int
     */
    protected $total_os_count;

    /**
     * Array of OS Information
     *
     * @var array
     */
    protected $os = [];

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
        $this->loadOSArray();
    }

    /**
     * listOS
     * Lists All Operating systems
     *
     *
     * @return string
     *
     */
    public function listOS()
    {
        return $this->api->makeAPICall('GET', $this->api::OS_URL);
    }

    /**
     * loadOSArray
     * Loads OS'es in to Array
     *
     *
     * @return void
     *
     */
    public function loadOSArray()
    {
        $osa = json_decode($this->listOS(), true);
        foreach ($osa['os'] as $os) {
            $id = $os['id'];
            $this->ids[] = $id;
            $this->os[$id]['name'] = $os['name'];
            $this->os[$id]['arch'] = $os['arch'];
            $this->os[$id]['family'] = $os['family'];
        }
        $this->total_os_count = $osa['meta']['total'];
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
}
