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

class SSHKeys
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array of All SSH Key IDs
     *
     * @var array<int>
     */
    public $ids = [];

    /**
     * Default label to use when creating SSH Key
     *
     * @var string
     */
    protected $d_label = '';

    /**
     * Array of SSH Key Information
     *
     * @var array<string>
     */
    public $sshKey = [];

    /**
     * Count of Total Keys
     *
     * @var int
     */
    protected $total_ssh_keys;

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadSSHKeys();
    }

    /**
     * listSSHKeys
     * Lists SSH Keys
     */
    public function listSSHKeys(): string
    {
        return $this->api->makeAPICall('GET', $this->api::SSH_KEYS_URL);
    }

    /**
     * deleteSSHKey
     * Deletes SSH Key
     */
    public function deleteSSHKey(string $id): string
    {
        return $this->api->makeAPICall('DELETE', $this->api::SSH_KEYS_URL . '/' . $id);
    }

    /**
     * getSSHKey
     * Get Snapshot Information
     */
    public function getSSHKey(string $id): string
    {
        return $this->api->makeAPICall('GET', $this->api::SSH_KEYS_URL . '/' . $id);
    }

    /**
     * loadSSHKeys
     * Loads Snapshot Information in to arrays
     */
    public function loadSSHKeys(): void
    {
        $ka = json_decode($this->listSSHKeys(), true);

        foreach ($ka['ssh_keys'] as $key) {
            $id = $key['id'];
            $this->ids[] = $id;
            $this->sshKey[$id] = $key;
        }
        $this->total_ssh_keys = $ka['meta']['total'];
    }

    /**
     * updateSSHKey
     * Updates SSH Key
     * @param array<string,string> $oa
     */
    public function updateSSHKey(array $oa): string
    {
        if (in_array($oa['id'], $this->ids, true)) {
            $url = $this->api::SSH_KEYS_URL . '/' . $oa['id'];
        } else {
            throw new InvalidParameterException("That SSH Key ID isn't associated with your account");
        }
        (isset($oa['name'])) ? $ba['name'] = $oa['name'] : null;
        (isset($oa['ssh_key'])) ? $ba['ssh_key'] = $oa['ssh_key'] : null;

        if (!isset($ba['name']) && !isset($ba['ssh_key'])) {
            throw new InvalidParameterException("You didn't provide any details to update - either a new key or a new description");
        }
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('PATCH', $url, $body);
    }

    /**
     * createSSHKey
     * Creates a SSH Key
     * @param array<string,string> $oa
     */
    public function createSSHKey(array $oa): string
    {
        if (!isset($oa['name'])) {
            throw new InvalidParameterException('Missing a name for your SSH Key');
        }

        if (!isset($oa['ssh_key'])) {
            throw new InvalidParameterException('Missing an SSH Key');
        }

        if (!$this->validateKey($oa['ssh_key'])) {
            throw new InvalidParameterException('Key is not a valid SSH Key');
        }
        $ba['ssh_key'] = $oa['ssh_key'];
        $ba['name'] = $oa['name'];
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::SSH_KEYS_URL, $body);
    }

    private function validateKey(string $value): bool
    {
        $key_parts = explode(' ', $value, 3);

        if (count($key_parts) < 2) {
            return false;
        }

        if (count($key_parts) > 3) {
            return false;
        }

        $algorithm = $key_parts[0];
        $key = $key_parts[1];

        if (!in_array($algorithm, ['ssh-rsa', 'ssh-dss'], true)) {
            return false;
        }
        $key_base64_decoded = base64_decode($key, true);

        if ($key_base64_decoded == false) {
            return false;
        }

        $check = base64_decode(substr($key, 0, 16), true) ?: '';
        $check = preg_replace("/[^\w\-]/", '', $check);

        if ((string) $check !== (string) $algorithm) {
            return false;
        }

        return true;
    }
}
