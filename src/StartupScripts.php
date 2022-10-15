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

use dutchie027\Vultr\Exceptions\InvalidParameterException;

class StartupScripts
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of All Script IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Array of All Script IDs
     *
     * @var array<string>
     */
    private $validStartupTypes = [
        'pxe',
        'boot',
    ];

    /**
     * Startup Type
     *
     * @var string
     */
    private $d_startup_type = 'boot';

    /**
     * Default label to use when creating script or updating Script
     *
     * @var string
     */
    protected $d_label = '';

    /**
     * Array of Startup Script Information
     *
     * @var array<string>
     */
    public $startupScripts = [];

    /**
     * Count of Total Scripts
     *
     * @var int
     */
    protected $total_startup_scripts;

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadStartupScripts();
    }

    /**
     * listStartupScripts
     * Lists Startup Scripts
     */
    public function listStartupScripts(): string
    {
        return $this->api->makeAPICall('GET', $this->api::STARTUP_SCRIPTS_URL);
    }

    /**
     * deleteStartupScript
     * Deletes Startup Script
     */
    public function deleteStartupScript(string $id): string
    {
        return $this->api->makeAPICall('DELETE', $this->api::STARTUP_SCRIPTS_URL . '/' . $id);
    }

    /**
     * getStartupScript
     * Get Startup Script Information
     */
    public function getStartupScript(string $id): string
    {
        return $this->api->makeAPICall('GET', $this->api::STARTUP_SCRIPTS_URL . '/' . $id);
    }

    /**
     * loadStartupScripts
     * Loads Startup Script Information in to arrays
     */
    public function loadStartupScripts(): void
    {
        $sa = json_decode($this->listStartupScripts(), true);

        foreach ($sa['startup_scripts'] as $startup) {
            $id = $startup['id'];
            $this->ids[] = $id;
            $this->startupScripts[$id] = $startup;
        }
        $this->total_startup_scripts = $sa['meta']['total'];
    }

    /**
     * updateStartupScript
     * Updates description of Snapshot
     * @param array<string,string> $oa
     */
    public function updateStartupScript(array $oa): string
    {
        $ba = [];

        if (in_array($oa['id'], $this->ids, true)) {
            $url = $this->api::STARTUP_SCRIPTS_URL . '/' . $oa['id'];
        } else {
            throw new InvalidParameterException("That Startup Script ID isn't associated with your account");
        }
        (isset($oa['name'])) ? $ba['name'] = $oa['name'] : null;
        (isset($oa['script'])) ? $ba['script'] = $oa['script'] : null;
        (isset($oa['type'])) ? $ba['type'] = $oa['type'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('PATCH', $url, $body);
    }

    /**
     * createStartupScript
     * Creates a Startup Script
     * @param array<string,string> $oa
     */
    public function createStartupScript(array $oa): string
    {
        if (!isset($oa['type'])) {
            $ba['type'] = $this->d_startup_type;
        } else {
            if (in_array($oa['type'], $this->validStartupTypes, true)) {
                $ba['type'] = $oa['type'];
            } else {
                throw new InvalidParameterException('Startup Script Type is invalid');
            }
        }

        if (!isset($oa['name'])) {
            throw new InvalidParameterException('Startup Script Name Required');
        }
        $ba['name'] = $oa['name'];

        if (!isset($oa['script'])) {
            throw new InvalidParameterException('Startup Script Missing');
        }
        $ba['script'] = base64_encode($oa['script']);

        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::STARTUP_SCRIPTS_URL, $body);
    }
}
