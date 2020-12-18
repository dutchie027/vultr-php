<?php

namespace dutchie027\Vultr;

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
    protected $d_label = "";

    /**
     * __construct
     * Main Construct - Loads Clusters and Objects in to arrays
     * and creates reference to main API
     *
     * @param $api
     *
     * @return void
     *
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
     *
     *
     * @return string
     *
     */
    public function listObjectStorage()
    {
        return $this->api->makeAPICall('GET', $this->api::OBJECT_STORAGE_URL);
    }

    /**
     * listObjectClusters
     * Lists object Clusters
     *
     *
     * @return string
     *
     */
    public function listObjectClusters()
    {
        return $this->api->makeAPICall('GET', $this->api::OBJECT_CLUSTERS_URL);
    }

    /**
     * loadClustersArrays
     * Loads Clusters in to an Array
     *
     *
     * @return void
     *
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
     *
     *
     * @return void
     *
     */
    public function loadObjectArrays()
    {
        $data = json_decode($this->listObjectStorage(), true);
        foreach ($data['object_storages'] as $line) {
            $this->storage_ids[] = $line['id'];
        }
    }

    /**
     * createObjectStorage
     * Creates Object Storage
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function createObjectStorage($options)
    {
        $ba['cluster_id'] = $this->d_cluster_id;
        $ba['label'] = $this->d_label;

        if (isset($options['cluster_id'])) {
            if (in_array($options['cluster_id'], $this->ids)) {
                $ba['cluster_id'] = $options['cluster_id'];
            } else {
                print "Bad Cluster ID";
            }
        } elseif (isset($options['region'])) {
            if (in_array($options['region'], $this->regions)) {
                $ba['cluster_id'] = $this->region_map[$options['region']];
            } else {
                print "Bad Region";
            }
        }

        (isset($options['label'])) ? $ba['label'] = $options['label'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::OBJECT_STORAGE_URL, $body);
    }

    /**
     * getObjectStorage
     * Gets Object Storage
     *
     * @param string $oid
     *
     * @return string
     *
     */
    public function getObjectStorage($oid)
    {
        if (in_array($oid, $this->storage_ids)) {
            return $this->api->makeAPICall('GET', $this->api::OBJECT_STORAGE_URL . "/" . $oid);
        } else {
            print "That Storage ID isn't associated with your account";
            exit;
        }
    }

    /**
     * deleteObjectStorage
     * Deletes Object Storage
     *
     * @param string $oid
     *
     * @return string
     *
     */
    public function deleteObjectStorage($oid)
    {
        if (in_array($oid, $this->storage_ids)) {
            return $this->api->makeAPICall('DELETE', $this->api::OBJECT_STORAGE_URL . "/" . $oid);
        } else {
            print "That Storage ID isn't associated with your account";
            exit;
        }
    }

    /**
     * regenerateKeys
     * Regenerates Object Storage Keys
     *
     * @param string $oid
     *
     * @return string
     *
     */
    public function regenerateKeys($oid)
    {
        if (in_array($oid, $this->storage_ids)) {
            $url = $this->api::OBJECT_STORAGE_URL . "/" . $oid . "/regenerate-keys";
        } else {
            print "That Storage ID isn't associated with your account";
            exit;
        }
        return $this->api->makeAPICall('POST', $url);
    }

    /**
     * updateObjectStorage
     * Updates label of Object Storage
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function updateObjectStorage($options)
    {
        if (in_array($options['object_id'], $this->storage_ids)) {
            $url = $this->api::OBJECT_STORAGE_URL . "/" . $options['object_id'];
        } else {
            print "That Storage ID isn't associated with your account";
            exit;
        }
        $ba['label'] = $this->d_label;
        (isset($options['label'])) ? $ba['label'] = $options['label'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('PUT', $url, $body);
    }
}
