<?php

namespace dutchie027\Vultr;

class Snapshots
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

     /**
     * Array of All Snapshot IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Default label to use when creating snapshot or updating snapshot
     *
     * @var string
     */
    protected $d_label = "";

    /**
     * Array of Snapshot Information
     *
     * @var array
     */
    public $snapshots = [];

    /**
     * Count of Total Snapshots
     *
     * @var int
     */
    protected $total_snapshots;

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
        $this->loadSnapshots();
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

     /**
     * listSnapshots
     * Lists Snapshots
     *
     *
     * @return string
     *
     */
    public function listSnapshots()
    {
        return $this->api->makeAPICall('GET', $this->api::SNAPSHOTS_URL);
    }

    /**
     * deleteSnapshot
     * Deletes Snapshot
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function deleteSnapshot($id)
    {
        return $this->api->makeAPICall('DELETE', $this->api::SNAPSHOTS_URL . "/" . $id);
    }

    /**
     * getSnapshot
     * Get Snapshot Information
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function getSnapshot($id)
    {
        return $this->api->makeAPICall('GET', $this->api::SNAPSHOTS_URL . "/" . $id);
    }

     /**
     * loadSnapshots
     * Loads Snapshot Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadSnapshots()
    {
        $sa = json_decode($this->listSnapshots(), true);
        foreach ($sa['snapshots'] as $snap) {
            $id = $snap['id'];
            $this->ids[] = $id;
            $this->snapshots[$id]['date_created'] = $snap['date_created'];
            $this->snapshots[$id]['description'] = $snap['description'];
            $this->snapshots[$id]['size'] = $snap['size'];
            $this->snapshots[$id]['status'] = $snap['status'];
            $this->snapshots[$id]['os_id'] = $snap['os_id'];
            $this->snapshots[$id]['app_id'] = $snap['app_id'];
        }
        $this->total_snapshots = $sa['meta']['total'];
    }

    /**
     * updateSnapshot
     * Updates description of Snapshot
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function updateSnapshot($options)
    {
        if (in_array($options['snapshot_id'], $this->ids)) {
            $url = $this->api::SNAPSHOTS_URL . "/" . $options['object_id'];
        } else {
            print "That Snapshot ID isn't associated with your account";
            exit;
        }
        $ba['description'] = $this->d_label;
        (isset($options['description'])) ? $ba['description'] = $options['description'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('PUT', $url, $body);
    }

    /**
     * createSnapshot
     * Creates a Snapshot
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function createSnapshot($oa)
    {
        if (!isset($oa['instance_id']) || !in_array($oa['instance_id'], $this->api->instances()->ids)) {
            print "Missing An Instance ID that is part of your account";
            exit;
        }
        $ba['instance_id'] = $oa['instance_id'];
        $ba['description'] = $this->d_label;
        (isset($oa['description'])) ? $ba['description'] = $oa['description'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::SNAPSHOTS_URL, $body);
    }

    /**
     * createSnapshotfromURL
     * Creates a Snapshot
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function createSnapshotFromURL($url)
    {
        $ba['url'] = $url;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::SNAPSHOTS_URL . "/create-from-url", $body);
    }
}
