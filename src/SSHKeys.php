<?php

/**
 * PHP Wrapper to Interact with Vultr 2.0 API
 *
 * @package Vultr
 * @version 2.0
 * @author  https://github.com/dutchie027
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @see     https://github.com/dutche027/vultr-php
 * @see     https://packagist.org/packages/dutchie027/vultr
 * @see     https://www.vultr.com/api/v2
 *
 */

namespace dutchie027\Vultr;

class SSHKeys
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array of All SSH Key IDs
     *
     * @var array
     */
    public $ids = [];

    /**
     * Default label to use when creating SSH Key
     *
     * @var string
     */
    protected $d_label = "";

    /**
     * Array of SSH Key Information
     *
     * @var array
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
     *
     * @param object $api API
     *
     * @return object
     *
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadSSHKeys();
    }

    /**
     * listSSHKeys
     * Lists SSH Keys
     *
     *
     * @return string
     *
     */
    public function listSSHKeys()
    {
        return $this->api->makeAPICall('GET', $this->api::SSH_KEYS_URL);
    }

    /**
     * deleteSSHKey
     * Deletes SSH Key
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function deleteSSHKey($id)
    {
        return $this->api->makeAPICall('DELETE', $this->api::SSH_KEYS_URL . "/" . $id);
    }

    /**
     * getSSHKey
     * Get Snapshot Information
     *
     * @var string $id
     *
     * @return string
     *
     */
    public function getSSHKey($id)
    {
        return $this->api->makeAPICall('GET', $this->api::SSH_KEYS_URL . "/" . $id);
    }

    /**
     * loadSSHKeys
     * Loads Snapshot Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadSSHKeys()
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
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function updateSSHKey($oa)
    {
        if (in_array($oa['id'], $this->ids)) {
            $url = $this->api::SSH_KEYS_URL . "/" . $oa['id'];
        } else {
            print "That SSH Key ID isn't associated with your account";
            exit;
        }
        (isset($oa['name'])) ? $ba['name'] = $oa['name'] : null;
        (isset($oa['ssh_key'])) ? $ba['ssh_key'] = $oa['ssh_key'] : null;
        if (!isset($ba['name']) && !isset($ba['ssh_key'])) {
            print "You didn't provide any details to update - either a new key or a new description";
            exit;
        } else {
            $body = json_encode($ba);
            return $this->api->makeAPICall('PATCH', $url, $body);
        }
    }

    /**
     * createSSHKey
     * Creates a SSH Key
     *
     * @param array $options
     *
     * @return string
     *
     */
    public function createSSHKey($oa)
    {
        if (!isset($oa['name'])) {
            print "Missing a name for your SSH Key";
            exit;
        }
        if (!isset($oa['ssh_key'])) {
            print "Missing an SSH Key";
            exit;
        }
        if (!$this->validateKey($oa['ssh_key'])) {
            print "Key is not a valid SSH Key";
            exit;
        }
        $ba['ssh_key'] = $oa['ssh_key'];
        $ba['name'] = $oa['name'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::SSH_KEYS_URL, $body);
    }

    private function validateKey($value)
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

        if (!in_array($algorithm, array('ssh-rsa', 'ssh-dss'))) {
            return false;
        }
        $key_base64_decoded = base64_decode($key, true);

        if ($key_base64_decoded == false) {
            return false;
        }

        $check = base64_decode(substr($key, 0, 16));
        $check = preg_replace("/[^\w\-]/", "", $check);

        if ((string) $check !== (string) $algorithm) {
            return false;
        }
        return true;
    }
}
