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

class Backups
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of Backup IDs
     *
     * @var array<int>
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
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadBackups();
    }

    /**
     * listBackups
     * Lists Backups
     */
    public function listBackups(): string
    {
        return $this->api->makeAPICall('GET', $this->api::BACKUPS_URL);
    }

    /**
     * loadBackups
     * Loads Backups in to an array
     */
    public function loadBackups(): void
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
     */
    public function getBackup(string $id): string
    {
        if (in_array($id, $this->ids, true)) {
            return $this->api->makeAPICall('GET', $this->api::BACKUPS_URL . '/' . $id);
        }

        throw new InvalidParameterException("That Backup ID isn't associated with your account");
    }

    /**
     * getNumberOfBackups
     * Returns total number of backups
     */
    public function getNumberOfBackups(): int
    {
        return $this->backup_count;
    }
}
