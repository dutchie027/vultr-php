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

class Snapshots
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of All Snapshot IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Default label to use when creating snapshot or updating snapshot
     *
     * @var string
     */
    protected $d_label = '';

    /**
     * Array of Snapshot Information
     *
     * @var array<string>
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
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadSnapshots();
    }

    /**
     * listSnapshots
     * Lists Snapshots
     */
    public function listSnapshots(): string
    {
        return $this->api->makeAPICall('GET', $this->api::SNAPSHOTS_URL);
    }

    /**
     * deleteSnapshot
     * Deletes Snapshot
     */
    public function deleteSnapshot(string $id): string
    {
        return $this->api->makeAPICall('DELETE', $this->api::SNAPSHOTS_URL . '/' . $id);
    }

    /**
     * getSnapshot
     * Get Snapshot Information
     */
    public function getSnapshot(string $id): string
    {
        return $this->api->makeAPICall('GET', $this->api::SNAPSHOTS_URL . '/' . $id);
    }

    /**
     * loadSnapshots
     * Loads Snapshot Information in to arrays
     */
    public function loadSnapshots(): void
    {
        $sa = json_decode($this->listSnapshots(), true);

        foreach ($sa['snapshots'] as $snap) {
            $id = $snap['id'];
            $this->ids[] = $id;
            $this->snapshots[$id] = $snap;
        }
        $this->total_snapshots = $sa['meta']['total'];
    }

    /**
     * updateSnapshot
     * Updates description of Snapshot
     * @param array<string,string> $options
     */
    public function updateSnapshot(array $options): string
    {
        if (in_array($options['snapshot_id'], $this->ids, true)) {
            $url = $this->api::SNAPSHOTS_URL . '/' . $options['snapshot_id'];
        } else {
            throw new InvalidParameterException("That Snapshot ID isn't associated with your account");
        }
        $ba['description'] = $this->d_label;
        (isset($options['description'])) ? $ba['description'] = $options['description'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('PUT', $url, $body);
    }

    /**
     * createSnapshot
     * Creates a Snapshot
     * @param array<string,string> $oa
     */
    public function createSnapshot(array $oa): string
    {
        if (!isset($oa['instance_id']) || !in_array($oa['instance_id'], $this->api->instances()->ids, true)) {
            throw new InvalidParameterException('Missing An Instance ID that is part of your account');
        }
        $ba['instance_id'] = $oa['instance_id'];
        $ba['description'] = $this->d_label;
        (isset($oa['description'])) ? $ba['description'] = $oa['description'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::SNAPSHOTS_URL, $body);
    }

    /**
     * createSnapshotfromURL
     * Creates a Snapshot
     */
    public function createSnapshotFromURL(string $url): string
    {
        $ba['url'] = $url;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::SNAPSHOTS_URL . '/create-from-url', $body);
    }
}
