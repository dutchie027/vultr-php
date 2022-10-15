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

class ObjectStorage
{
    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array containing block IDs
     *
     * @var array
     */
    protected $ids = [];

    /**
     * Array containing Regions
     *
     * @var array
     */
    protected $regions = [];

    /**
     * Array Mapping Region Names to IDs
     *
     * @var array
     */
    protected $region_map = [];

    /**
     * Array containing Storage IDs
     *
     * @var array
     */
    protected $storage_ids = [];

    /**
     * Default Cluster ID to use
     *
     * @var int
     */
    protected $d_cluster_id = 2;

    /**
     * Default label to use when creating object storage
     *
     * @var string
     */
    protected $d_label = '';

    /**
     * __construct
     * Main Construct - Loads Clusters and Objects in to arrays
     * and creates reference to main API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadClustersArrays();
        $this->loadObjectArrays();
    }

    /**
     * listObjectStorage
     * Lists Object Storage
     */
    public function listObjectStorage(): string
    {
        return $this->api->makeAPICall('GET', $this->api::OBJECT_STORAGE_URL);
    }

    /**
     * listObjectClusters
     * Lists object Clusters
     */
    public function listObjectClusters(): string
    {
        return $this->api->makeAPICall('GET', $this->api::OBJECT_CLUSTERS_URL);
    }

    /**
     * loadClustersArrays
     * Loads Clusters in to an Array
     */
    public function loadClustersArrays()
    {
        $data = json_decode($this->listObjectClusters(), true);

        foreach ($data['clusters'] as $line) {
            $region = $line['region'];
            $this->ids[] = $line['id'];
            $this->regions[] = $region;
            $this->region_map[$region] = $line['id'];
        }
    }

    /**
     * loadObjectArrays
     * Load Object Stores in to array
     */
    public function loadObjectArrays(): void
    {
        $data = json_decode($this->listObjectStorage(), true);

        foreach ($data['object_storages'] as $line) {
            $this->storage_ids[] = $line['id'];
        }
    }

    /**
     * createObjectStorage
     * Creates Object Storage
     */
    public function createObjectStorage(array $options): string
    {
        $ba['cluster_id'] = $this->d_cluster_id;
        $ba['label'] = $this->d_label;

        if (isset($options['cluster_id'])) {
            if (in_array($options['cluster_id'], $this->ids, true)) {
                $ba['cluster_id'] = $options['cluster_id'];
            } else {
                throw new InvalidParameterException('Bad Cluster ID');
            }
        } elseif (isset($options['region'])) {
            if (in_array($options['region'], $this->regions, true)) {
                $ba['cluster_id'] = $this->region_map[$options['region']];
            } else {
                throw new InvalidParameterException('Bad Region');
            }
        }

        (isset($options['label'])) ? $ba['label'] = $options['label'] : null;
        $body = json_encode($ba);

        return $this->api->makeAPICall('POST', $this->api::OBJECT_STORAGE_URL, $body);
    }

    /**
     * getObjectStorage
     * Gets Object Storage
     */
    public function getObjectStorage(string $oid): string
    {
        if (in_array($oid, $this->storage_ids, true)) {
            return $this->api->makeAPICall('GET', $this->api::OBJECT_STORAGE_URL . '/' . $oid);
        }

        throw new InvalidParameterException("That Storage ID isn't associated with your account");
    }

    /**
     * deleteObjectStorage
     * Deletes Object Storage
     */
    public function deleteObjectStorage(string $oid): string
    {
        if (in_array($oid, $this->storage_ids, true)) {
            return $this->api->makeAPICall('DELETE', $this->api::OBJECT_STORAGE_URL . '/' . $oid);
        }

        throw new InvalidParameterException("That Storage ID isn't associated with your account");
    }

    /**
     * regenerateKeys
     * Regenerates Object Storage Keys
     */
    public function regenerateKeys(string $oid): string
    {
        if (in_array($oid, $this->storage_ids, true)) {
            $url = $this->api::OBJECT_STORAGE_URL . '/' . $oid . '/regenerate-keys';
        } else {
            throw new InvalidParameterException("That Storage ID isn't associated with your account");
        }

        return $this->api->makeAPICall('POST', $url);
    }

    /**
     * updateObjectStorage
     * Updates label of Object Storage
     */
    public function updateObjectStorage(array $options): string
    {
        if (in_array($options['object_id'], $this->storage_ids, true)) {
            $url = $this->api::OBJECT_STORAGE_URL . '/' . $options['object_id'];
        } else {
            throw new InvalidParameterException("That Storage ID isn't associated with your account");
        }
        $ba['label'] = $this->d_label;
        (isset($options['label'])) ? $ba['label'] = $options['label'] : null;
        $body = json_encode($ba);

        return $this->api->makeAPICall('PUT', $url, $body);
    }
}
