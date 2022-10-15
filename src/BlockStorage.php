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

class BlockStorage
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Total number of block storages
     *
     * @var int
     */
    protected $total_blocks;

    /**
     * Default Region to user
     *
     * @var string
     */
    private $d_region = 'ewr';

    /**
     * Default size (in GB)
     *
     * @var int
     */
    private $d_size = 20;

    /**
     * Default label
     *
     * @var string
     */
    private $d_label = '';

    /**
     * Array containing block IDs
     *
     * @var array<int>
     */
    public $block_array = [];

    /**
     * __construct
     * Main Construct - Loads Regions in to arrays and creates reference
     * from main API class
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadBlocks();
    }

    /**
     * listBlockStorage
     * Lists All Block Storage
     */
    public function listBlockStorage(): string
    {
        return $this->api->makeAPICall('GET', $this->api::BLOCK_STORAGE_URL);
    }

    /**
     * loadBlocks
     * Loads Blocks Used in to Array
     */
    public function loadBlocks(): void
    {
        $ba = json_decode($this->listBlockStorage(), true);

        foreach ($ba['blocks'] as $bsa) {
            $this->block_array[] = $bsa['id'];
        }
        $this->total_blocks = $ba['meta']['total'];
    }

    /**
     * createBlockStorage
     * Creates Block Storage
     * @param array<string,string> $sa
     */
    public function createBlockStorage(array $sa = []): string
    {
        $block_ids = $this->api->regions()->getBlockIds();

        $ba['region'] = $this->d_region;
        $ba['size_gb'] = $this->d_size;
        $ba['label'] = $this->d_label;

        (isset($sa['region']) && in_array($sa['region'], $block_ids, true)) ? $ba['region'] = $sa['region'] : null;

        if (isset($sa['size']) && is_numeric($sa['size'])) {
            if ($sa['size'] > 9 && $sa['size'] < 10001) {
                $ba['size_gb'] = $sa['size'];
            }
        }
        (isset($sa['label'])) ? $ba['label'] = $sa['label'] : null;
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::BLOCK_STORAGE_URL, $body);
    }

    /**
     * getBlockStorage
     * Gets information on block storage
     */
    public function getBlockStorage(string $blockid): string
    {
        if (in_array($blockid, $this->block_array, true)) {
            return $this->api->makeAPICall('GET', $this->api::BLOCK_STORAGE_URL . '/' . $blockid);
        }

        throw new InvalidParameterException("That Block ID isn't associated with your account");
    }

    /**
     * deleteBlockStorage
     * Deletes Block Storage
     */
    public function deleteBlockStorage(string $blockid): string
    {
        if (in_array($blockid, $this->block_array, true)) {
            return $this->api->makeAPICall('DELETE', $this->api::BLOCK_STORAGE_URL . '/' . $blockid);
        }

        throw new InvalidParameterException("That Block ID isn't associated with your account");
    }

    /**
     * updateBlockStorage
     * Updates Block Storage Size and Label
     * @param array<string,string> $options
     */
    public function updateBlockStorage(array $options): string
    {
        if (in_array($options['blockid'], $this->block_array, true)) {
            if (!isset($options['size'])) {
                throw new InvalidParameterException('You must set the size');
            }

            if (!is_numeric($options['size'])) {
                throw new InvalidParameterException('Size must be numeric');
            }

            if ($options['size'] < 10 || $options['size'] > 10000) {
                throw new InvalidParameterException('Size must be a number between 10 and 10000');
            }
            $ba['size_gb'] = $options['size'];

            (isset($options['label'])) ? $ba['label'] = $options['label'] : null;

            $body = $this->api->returnJSONBody($ba);

            return $this->api->makeAPICall('PATCH', $this->api::BLOCK_STORAGE_URL . '/' . $options['block_id'], $body);
        }

        throw new InvalidParameterException("That block ID doesn't exist in your account");
    }

    /**
     * attachBlockStorage
     * Attaches Block Storage to an Instance
     * @param array<string,mixed> $options
     */
    public function attachBlockStorage(array $options): string
    {
        $instance_ids = $this->api->instances()->getIds();

        if (in_array($options['instance'], $instance_ids, true)) {
            if (!in_array($options['blockid'], $this->block_array, true)) {
                throw new InvalidParameterException("That block ID doesn't exist in your account");
            }

            if (!isset($options['live'])) {
                throw new InvalidParameterException('You must set the live variable');
            }

            if (!is_bool($options['live'])) {
                throw new InvalidParameterException('The live setting must be either true or false only.');
            }
            $ba['instance_id'] = $options['instance'];
            $ba['live'] = $options['live'];

            $url = $this->api::BLOCK_STORAGE_URL . '/' . $options['block_id'] . '/attach';

            $body = $this->api->returnJSONBody($ba);

            return $this->api->makeAPICall('POST', $url, $body);
        }

        throw new InvalidParameterException("That block ID doesn't exist in your account");
    }

    /**
     * detatchBlockStorage
     * Detatches Block Storage from Instance
     * @param array<string,string> $options
     */
    public function detatchBlockStorage(array $options): string
    {
        if (in_array($options['blockid'], $this->block_array, true)) {
            if (!isset($options['live'])) {
                throw new InvalidParameterException('You must set the live variable');
            }

            if (!is_bool($options['live'])) {
                throw new InvalidParameterException('The live setting must be either true or false only.');
            }
            $ba['live'] = $options['live'];

            $url = $this->api::BLOCK_STORAGE_URL . '/' . $options['block_id'] . '/detatch';

            $body = $this->api->returnJSONBody($ba);

            return $this->api->makeAPICall('POST', $url, $body);
        }

        throw new InvalidParameterException("That block ID doesn't exist in your account");
    }

    /**
     * getNumberOfBlocks
     * Returns total number of blocks
     */
    public function getNumberOfBlocks(): int
    {
        return $this->total_blocks;
    }
}
