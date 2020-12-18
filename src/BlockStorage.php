<?php

namespace dutchie027\Vultr;

class BlockStorage
{

    protected $api;
    private $d_region = "ewr";
    private $d_size = 20;
    private $d_label = "";

    public $block_array = array();

    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadBlocks();
    }

    public function listBlockStorage()
    {
        return $this->api->makeAPICall('GET', $this->api::BLOCK_STORAGE_URL);
    }

    public function loadBlocks()
    {
        $ba = json_decode($this->listBlockStorage(), true);
        foreach ($ba['blocks'] as $bsa) {
            $this->block_array[] = $bsa['id'];
        }
    }

    public function createBlockStorage($sa = [])
    {
        $block_ids = $this->api->regions()->getBlockIds();

        $ba['region'] = $this->d_region;
        $ba['size_gb'] = $this->d_size;
        $ba['label'] = $this->d_label;

        print_r($block_ids);

        (isset($sa['region']) && in_array($sa['region'], $block_ids)) ? $ba['region'] = $sa['region'] : null;
        if (isset($sa['size']) && is_numeric($sa['size'])) {
            if ($sa['size'] > 9 && $sa['size'] < 10001) {
                $ba['size_gb'] = $sa['size'];
            }
        }
        (isset($sa['label'])) ? $ba['label'] = $sa['label'] : null;
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::BLOCK_STORAGE_URL, $body);
    }

    public function getBlockStorage($blockid)
    {
        if (in_array($blockid, $this->block_array)) {
            return $this->api->makeAPICall('GET', $this->api::BLOCK_STORAGE_URL . "/" . $blockid);
        } else {
            print "That Block ID isn't associated with your account";
            exit;
        }
    }

    public function deleteBlockStorage($blockid)
    {
        if (in_array($blockid, $this->block_array)) {
            return $this->api->makeAPICall('DELETE', $this->api::BLOCK_STORAGE_URL . "/" . $blockid);
        } else {
            print "That Block ID isn't associated with your account";
            exit;
        }
    }

    public function updateBlockStorage($options)
    {
        if (in_array($options['blockid'], $this->block_array)) {
            if (!isset($options['size'])) {
                print "you must set the size";
                exit;
            } elseif (!is_numeric($options['size'])) {
                print "size must be numeric";
                exit;
            } elseif ($options['size'] < 10 || $options['size'] > 10000) {
                print "Size must be a number between 10 and 10000";
                exit;
            } else {
                $ba['size_gb'] = $options['size'];
            }
            (isset($sa['label'])) ? $ba['label'] = $sa['label'] : null;

            $body = json_encode($ba);
            return $this->api->makeAPICall('PATCH', $this->api::BLOCK_STORAGE_URL . "/" . $options['block_id'], $body);
        } else {
            print "That block ID doesn't exist in your account";
            exit;
        }
    }

    public function attachBlockStorage($options)
    {
        $instance_ids = $this->api->instances()->getIds();
        if (in_array($options['instance'], $instance_ids)) {
            if (!in_array($options['blockid'], $this->block_array)) {
                print "That block ID doesn't exist in your account";
                exit;
            } elseif (!isset($options['live'])) {
                print "you must set the live variable";
                exit;
            } elseif (!is_bool($options['live'])) {
                print "the live setting must be either true or false only.";
                exit;
            }
            $ba['instance_id'] = $options['instance'];
            $ba['live'] = $options['live'];

            $url = $this->api::BLOCK_STORAGE_URL . "/" . $options['block_id'] . "/attach";

            $body = json_encode($ba);
            return $this->api->makeAPICall('POST', $url, $body);
        } else {
            print "That block ID doesn't exist in your account";
            exit;
        }
    }

    public function detatchBlockStorage($options)
    {
        if (in_array($options['blockid'], $this->block_array)) {
            if (!isset($options['live'])) {
                print "you must set the live variable";
                exit;
            } elseif (!is_bool($options['live'])) {
                print "the live setting must be either true or false only.";
                exit;
            } else {
                $ba['live'] = $options['live'];
            }

            $url = $this->api::BLOCK_STORAGE_URL . "/" . $options['block_id'] . "/detatch";

            $body = json_encode($ba);
            return $this->api->makeAPICall('POST', $url, $body);
        } else {
            print "That block ID doesn't exist in your account";
            exit;
        }
    }
}
