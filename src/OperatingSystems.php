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

class OperatingSystems
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of All OS IDs
     *
     * @var array<int>
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
     * @var array<string>
     */
    protected $os = [];

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadOSArray();
    }

    /**
     * listOS
     * Lists All Operating systems
     */
    public function listOS(): string
    {
        return $this->api->makeAPICall('GET', $this->api::OS_URL);
    }

    /**
     * loadOSArray
     * Loads OS'es in to Array
     */
    public function loadOSArray(): void
    {
        $osa = json_decode($this->listOS(), true);

        foreach ($osa['os'] as $os) {
            $id = $os['id'];
            $this->ids[] = $id;
            $this->os[$id] = $os;
        }
        $this->total_os_count = $osa['meta']['total'];
    }
}
