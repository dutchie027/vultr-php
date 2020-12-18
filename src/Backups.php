<?php

namespace dutchie027\Vultr;

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
    protected $ids = [];

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
            print "That Backup ID isn't associated with your account";
            exit;
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
