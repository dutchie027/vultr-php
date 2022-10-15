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
use dutchie027\Vultr\Exceptions\VultrAPIException;

class Users
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Array containing user data
     *
     * @var array<string>
     */
    protected $users = [];

    /**
     * Array containing User IDs
     *
     * @var array<int>
     */
    protected $ids = [];

    /**
     * Total Number of Users
     *
     * @var int
     */
    protected $total_users = 0;

    /**
     * Default ACL for creaging a user
     *
     * @var array<mixed>
     */
    protected $d_acl = [
        'acls' => [
            'subscriptions_view',
        ],
    ];

    /**
     * Default Value for API Enabled when Creating
     * A User
     *
     * @var bool
     */
    protected $d_api_enabled = false;

    /**
     * Array containing valid ACLs when creating a user
     *
     * @var array<string>
     */
    private $valid_acls = [
        'abuse',
        'alerts',
        'billing',
        'dns',
        'firewall',
        'loadbalancer',
        'manage_users',
        'objstore',
        'provisioning',
        'subscriptions',
        'subscriptions_view',
        'support',
        'upgrade',
    ];

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
        $this->loadUsers();
    }

    /**
     * getUser
     * Gets information on a specific user based on their ID
     */
    public function getUser(string $id): string
    {
        if (in_array($id, $this->ids, true)) {
            return $this->api->makeAPICall('GET', $this->api::USERS_URL . '/' . $id);
        }

        throw new InvalidParameterException("That User ID isn't associated with your account");
    }

    /**
     * getUsers
     * Gets All users
     */
    public function getUsers(): string
    {
        return $this->api->makeAPICall('GET', $this->api::USERS_URL);
    }

    /**
     * loadUsers
     * Loads User Information in to arrays
     */
    public function loadUsers(): void
    {
        $ua = json_decode($this->getUsers(), true);

        foreach ($ua['users'] as $usr) {
            $id = $usr['id'];
            $this->ids[] = $id;
            $this->users[$id] = $usr;
        }
        $this->total_users = $ua['meta']['total'];
    }

    /**
     * updateuser
     * Updates A User
     * @param array<string,string> $oa
     */
    public function updateUser(array $oa): string
    {
        if (in_array($oa['id'], $this->ids, true)) {
            $url = $this->api::USERS_URL . '/' . $oa['id'];
        } else {
            throw new InvalidParameterException("That User ID doesn't exist");
        }
        $cr = false;
        $ba = [];

        if (isset($oa['email'])) {
            if (filter_var($oa['email'], FILTER_VALIDATE_EMAIL)) {
                $cr = true;
                $ba['email'] = $oa['email'];
            } else {
                throw new InvalidParameterException('Email included but is invalid');
            }
        }

        if (isset($oa['password'])) {
            if (strlen($oa['password']) < 8) {
                throw new InvalidParameterException('Password included but is less than 8 characters');
            }
            $cr = true;
            $ba['password'] = $oa['password'];
        }

        if (isset($oa['name'])) {
            if (strlen($oa['name']) < 4) {
                throw new InvalidParameterException('Name included but is less than 4 characters');
            }
            $cr = true;
            $ba['name'] = $oa['name'];
        }

        if (isset($oa['api_enabled'])) {
            if (!is_bool($oa['api_enabled'])) {
                throw new InvalidParameterException('API Enabled Flag Is Invalid');
            }
            $cr = true;
            $ba['api_enabled'] = $oa['api_enabled'];
        }

        if (isset($oa['acls'])) {
            if (is_array($oa['acls'])) {
                $acl_valid = true;

                foreach ($oa['acls'] as $acl) {
                    if (!in_array($acl, $this->valid_acls, true)) {
                        $acl_valid = false;
                    }
                }

                if (!$acl_valid) {
                    throw new InvalidParameterException('Invalid ACLS');
                }
                $ba['acls'] = $oa['acls'];
                $cr = true;
            } else {
                throw new InvalidParameterException('ACL Value(s) passed invalid. Must be an array');
            }
        }

        if ($cr) {
            $body = $this->api->returnJSONBody($ba);

            return $this->api->makeAPICall('PATCH', $url, $body);
        }

        throw new VultrAPIException('Nothing to PATCH on line ' . __LINE__);
    }

    /**
     * createUser
     * Creates a User
     * @param array<string,string> $oa
     */
    public function createUser(array $oa): string
    {
        $ba['api_enabled'] = $this->d_api_enabled;
        $ba['acls'] = $this->d_acl;

        if (!isset($oa['email']) || !filter_var($oa['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidParameterException('Email required or is invalid');
        }
        $ba['email'] = $oa['email'];

        if (!isset($oa['password']) || strlen($oa['password']) < 8) {
            throw new InvalidParameterException('Password Required or is less than 8 characters');
        }
        $ba['password'] = $oa['password'];

        if (!isset($oa['name']) || strlen($oa['name']) < 4) {
            throw new InvalidParameterException('Name Required or is less than 4 characters');
        }
        $ba['name'] = $oa['name'];

        if (isset($oa['acls']) && is_array($oa['acls'])) {
            $acl_valid = true;

            foreach ($oa['acls'] as $acl) {
                if (!in_array($acl, $this->valid_acls, true)) {
                    $acl_valid = false;
                }
            }

            if (!$acl_valid) {
                throw new InvalidParameterException('Invalid ACLS');
            }
            $ba['acls'] = $oa['acls'];
        }

        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::USERS_URL, $body);
    }

    /**
     * deleteUser
     * Deletes User
     */
    public function deleteUser(string $id): string
    {
        if (in_array($id, $this->ids, true)) {
            return $this->api->makeAPICall('DELETE', $this->api::USERS_URL . '/' . $id);
        }

        throw new InvalidParameterException("That User ID isn't associated with your account");
    }

    /**
     * getNumberOfUsers
     * Returns total number of users
     */
    public function getNumberOfUsers(): int
    {
        return $this->total_users;
    }
}
