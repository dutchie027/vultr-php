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

use dutchie027\Vultr\Exceptions\InvalidParameterException;

class Backups
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of Backup IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Total Number of Backups
     *
     * @var int
     */
    protected $backup_count;

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
        $this->loadBackups();
    }

    /**
     * listBackups
     * Lists Backups
     *
     *
     * @return string
     *
     */
    public function listBackups()
    {
        return $this->api->makeAPICall('GET', $this->api::BACKUPS_URL);
    }

    /**
     * loadBackups
     * Loads Backups in to an array
     *
     *
     * @return void
     *
     */
    public function loadBackups()
    {
        $backups = json_decode($this->listBackups(), true);
        foreach ($backups['backups'] as $bu) {
            $this->ids[] = $bu['id'];
        }
        $this->backup_count = $backups['meta']['total'];
    }

    /**
     * getBackup
     * Gets information on a backup
     *
     * @param string $id
     *
     * @return string
     *
     */
    public function getBackup($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->api->makeAPICall('GET', $this->api::BACKUPS_URL . "/" . $id);
        } else {
            throw new InvalidParameterException("That Backup ID isn't associated with your account");
        }
    }

    /**
     * getNumberOfBackups
     * Returns total number of backups
     *
     *
     * @return int
     *
     */
    public function getNumberOfBackups()
    {
        return $this->backup_count;
    }
}
