<?php

namespace dutchie027\Vultr;

class Users
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Array containing user data
     *
     * @var array
     */
    protected $users = [];

    /**
     * Array containing User IDs
     *
     * @var array
     */
    protected $ids = [];

    /**
     * Default ACL for creaging a user
     *
     * @var array
     */
    protected $d_acl = [
        'acls' => [
            'subscriptions_view',
        ]
    ];

    /**
     * Default Value for API Enabled when Creating
     * A User
     *
     * @var string
     */
    protected $d_api_enabled = false;

    /**
     * Array containing valid ACLs when creating a user
     *
     * @var array
     */
    private $valid_acls = [
        "abuse",
        "alerts",
        "billing",
        "dns",
        "firewall",
        "loadbalancer",
        "manage_users",
        "objstore",
        "provisioning",
        "subscriptions",
        "subscriptions_view",
        "support",
        "upgrade"
    ];

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
        $this->loadUsers();
    }

    /**
     * getUser
     * Gets information on a specific user based on their ID
     *
     * @param string $id
     *
     * @return string
     *
     */
    public function getUser($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->api->makeAPICall('GET', $this->api::USERS_URL . "/" . $id);
        } else {
            print "That User ID isn't associated with your account";
            exit;
        }
    }

    /**
     * getUsers
     * Gets All users
     *
     *
     * @return string
     *
     */
    public function getUsers()
    {
        return $this->api->makeAPICall('GET', $this->api::USERS_URL);
    }

    /**
     * loadusers
     * Loads User Information in to arrays
     *
     *
     * @return void
     *
     */
    public function loadUsers()
    {
        $ua = json_decode($this->getUsers(), true);
        foreach ($ua['users'] as $usr) {
            $id = $usr['id'];
            $this->ids[] = $id;
            $this->users[$id]['email'] = $usr['email'];
            $this->users[$id]['api_enabled'] = $usr['api_enabled'];
            $this->users[$id]['acls'] = $usr['acls'];
        }
        $this->total_blocks = $ua['meta']['total'];
    }

    /**
     * updateuser
     * Updates A User
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function updateUser($oa)
    {
        if (in_array($oa['id'], $this->ids)) {
            $url = $this->api::USERS_URL . "/" . $oa['id'];
        } else {
            print "That User ID doesn't exist";
            exit;
        }
        $cr = false;
        if (isset($oa['email'])) {
            if (filter_var($oa['email'], FILTER_VALIDATE_EMAIL)) {
                $cr = true;
                $ba['email'] = $oa['email'];
            } else {
                print "Email included but is invalid";
                exit;
            }
        }
        if (isset($oa['password'])) {
            if (strlen($oa['password']) < 8) {
                print "Password included but is less than 8 characters";
                exit;
            } else {
                $cr = true;
                $ba['password'] = $oa['password'];
            }
        }
        if (isset($oa['name'])) {
            if (strlen($oa['name']) < 4) {
                print "Name included but is less than 4 characters";
                exit;
            } else {
                $cr = true;
                $ba['name'] = $oa['name'];
            }
        }
        if (isset($oa['api_enabled'])) {
            if (!is_bool($oa['api_enabled'])) {
                print "API Enabled Flag Is Invalid";
                exit;
            } else {
                $cr = true;
                $ba['api_enabled'] = $oa['api_enabled'];
            }
        }
        if (isset($oa['acls'])) {
            if (is_array($oa['acls'])) {
                $acl_valid = true;
                foreach ($oa['acls'] as $acl) {
                    if (!in_array($acl, $this->valid_acls)) {
                        $acl_valid = false;
                    }
                }
                if (!$acl_valid) {
                    print "Invalid ACLS";
                    exit;
                } else {
                    $ba['acls'] = $oa['acls'];
                    $cr = true;
                }
            } else {
                print "ACL Value(s) passed invalid. Must be an array";
            }
        }
        if ($cr) {
            $body = json_encode($ba);
            return $this->api->makeAPICall('PATCH', $url, $body);
        }
    }

    /**
     * createUser
     * Creates a User
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function createUser($oa)
    {
        $ba['api_enabled'] = $this->d_api_enabled;
        $ba['acls'] = $this->d_acl;
        if (!isset($oa['email']) || !filter_var($oa['email'], FILTER_VALIDATE_EMAIL)) {
            print "Email required or is invalid";
            exit;
        } else {
            $ba['email'] = $oa['email'];
        }
        if (!isset($oa['password']) || strlen($oa['password']) < 8) {
            print "Password Required or is less than 8 characters";
            exit;
        } else {
            $ba['password'] = $oa['password'];
        }
        if (!isset($oa['name']) || strlen($oa['name']) < 4) {
            print "Name Required or is less than 4 characters";
            exit;
        } else {
            $ba['name'] = $oa['name'];
        }
        if (isset($oa['acls']) && is_array($oa['acls'])) {
            $acl_valid = true;
            foreach ($oa['acls'] as $acl) {
                if (!in_array($acl, $this->valid_acls)) {
                    $acl_valid = false;
                }
            }
            if (!$acl_valid) {
                print "Invalid ACLS";
                exit;
            } else {
                $ba['acls'] = $oa['acls'];
            }
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::USERS_URL, $body);
    }

    /**
     * deleteUser
     * Deletes User
     *
     * @param string $id
     *
     * @return string
     *
     */
    public function deleteUser($id)
    {
        if (in_array($id, $this->ids)) {
            return $this->api->makeAPICall('DELETE', $this->api::USERS_URL . "/" . $id);
        } else {
            print "That User ID isn't associated with your account";
            exit;
        }
    }

    /**
     * getNumberOfUsers
     * Returns total number of users
     *
     *
     * @return int
     *
     */
    public function getNumberOfUsers()
    {
        return $this->total_users;
    }
}
