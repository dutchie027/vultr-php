<?php

namespace dutchie027\Vultr;

class ObjectStorage
{
    protected $api;

    protected $ids = [];
    protected $regions = [];
    protected $region_map = [];
    protected $storage_ids = [];

    protected $d_cluster_id = 2;
    protected $d_label = "";

    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadClustersArrays();
        $this->loadObjectArrays();
    }

    public function listObjectStorage()
    {
        return $this->api->makeAPICall('GET', $this->api::OBJECT_STORAGE_URL);
    }

    public function listObjectClusters()
    {
        return $this->api->makeAPICall('GET', $this->api::OBJECT_CLUSTERS_URL);
    }

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

    public function loadObjectArrays()
    {
        $data = json_decode($this->listObjectStorage(), true);
        foreach ($data['object_storages'] as $line) {
            $this->storage_ids[] = $line['id'];
        }
    }

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

    public function getObjectStorage($oid)
    {
        if (in_array($oid, $this->storage_ids)) {
            return $this->api->makeAPICall('GET', $this->api::OBJECT_STORAGE_URL . "/" . $oid);
        } else {
            print "That Storage ID isn't associated with your account";
            exit;
        }
    }

    public function deleteObjectStorage($oid)
    {
        if (in_array($oid, $this->storage_ids)) {
            return $this->api->makeAPICall('DELETE', $this->api::OBJECT_STORAGE_URL . "/" . $oid);
        } else {
            print "That Storage ID isn't associated with your account";
            exit;
        }
    }

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
