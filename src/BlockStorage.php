<?php

namespace dutchie027\Vultr;

class BlockStorage
{

    protected $api;
    private $d_region = "ewr";
    private $d_size = 20;
    private $d_label = "";

    public function __construct(API $api)
    {
        $this->api = $api;
    }

    public function listBlockStorage()
    {
        return $this->api->makeAPICall('GET', $this->api::BLOCK_STORAGE_URL);
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

    public function getBlockStorage()
    {
        $data['headers'] = $this->api->setHeaders();
        $response = $this->api->guzzle->request('GET', $this->api::BLOCK_STORAGE_URL, $data);
        return $response->getBody();
    }

    public function deleteBlockStorage()
    {
        $data['headers'] = $this->api->setHeaders();
        $response = $this->api->guzzle->request('DEL', $this->api::BLOCK_STORAGE_URL, $data);
        return $response->getBody();
    }

    public function updateBlockStorage()
    {
        $data['headers'] = $this->api->setHeaders();
        $response = $this->api->guzzle->request('PATCH', $this->api::BLOCK_STORAGE_URL, $data);
        return $response->getBody();
    }

    public function attachBlockStorage()
    {
        $data['headers'] = $this->api->setHeaders();
        $response = $this->api->guzzle->request('POST', $this->api::BLOCK_STORAGE_URL, $data);
        return $response->getBody();
    }

    public function detatchBlockStorage()
    {
        $data['headers'] = $this->api->setHeaders();
        $response = $this->api->guzzle->request('POST', $this->api::BLOCK_STORAGE_URL, $data);
        return $response->getBody();
    }
}