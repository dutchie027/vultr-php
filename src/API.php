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

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class API
{
    /**
     * Version of the Library
     *
     * @const string
     */
    protected const LIBRARY_VERSION = '1.9.0';

    /**
     * Root of the API
     *
     * @const string
     */
    protected const API_URL = 'https://api.vultr.com/v2';

    /**
     * Endpoint for Account API
     *
     * @const string
     */
    public const ACCOUNT_URL = self::API_URL . '/account';

    /**
     * Endpoint for Account API
     *
     * @const string
     */
    public const APPLICATIONS_URL = self::API_URL . '/applications';

    /**
     * Endpoint for Account API
     *
     * @const string
     */
    public const BACKUPS_URL = self::API_URL . '/backups';

    /**
     * Endpoint for Bare Metal
     *
     * @const string
     */
    public const BARE_METAL_URL = self::API_URL . '/bare-metals';

    /**
     * Endpoint for Block Storage
     *
     * @const string
     */
    public const BLOCK_STORAGE_URL = self::API_URL . '/blocks';

    /**
     * Endpoint for DNS
     *
     * @const string
     */
    public const DNS_URL = self::API_URL . '/domains';

    /**
     * Endpoint for Firewalls
     *
     * @const string
     */
    public const FIREWALLS_URL = self::API_URL . '/firewalls';

    /**
     * Endpoint for Instances API
     *
     * @const string
     */
    public const INSTANCES_URL = self::API_URL . '/instances';

    /**
     * Endpoint for ISO API
     *
     * @const string
     */
    public const ISO_URL = self::API_URL . '/iso';

    /**
     * Endpoint for Public ISO API
     *
     * @const string
     */
    public const ISO_PUBLIC_URL = self::API_URL . '/iso-public';

    /**
     * Endpoint for Load Balacers
     *
     * @const string
     */
    public const LOAD_BALANCERS_URL = self::API_URL . '/load-balancers';

    /**
     * Endpoint for Object Storage API
     *
     * @const string
     */
    public const OBJECT_STORAGE_URL = self::API_URL . '/object-storage';

    /**
     * Endpoint for Object Storage Clusters
     *
     * @const string
     */
    public const OBJECT_CLUSTERS_URL = self::OBJECT_STORAGE_URL . '/clusters';

    /**
     * Endpoint for Operating Systems
     *
     * @const string
     */
    public const OS_URL = self::API_URL . '/os';

    /**
     * Endpoint for Plans
     *
     * @const string
     */
    public const PLANS_URL = self::API_URL . '/plans';

    /**
     * Endpoint for Bare Metal Plans
     *
     * @const string
     */
    public const METAL_PLANS_URL = self::API_URL . '/plans-metal';

    /**
     * Endpoint for Private Networks
     *
     * @const string
     */
    public const PRIVATE_NETWORKS_URL = self::API_URL . '/private-networks';

    /**
     * Endpoint for Reserved IPs
     *
     * @const string
     */
    public const RESERVED_IPS_URL = self::API_URL . '/reserved-ips';

    /**
     * Endpoint for Regions API
     *
     * @const string
     */
    public const REGIONS_URL = self::API_URL . '/regions';

    /**
     * Endpoint for Snapshots
     *
     * @const string
     */
    public const SNAPSHOTS_URL = self::API_URL . '/snapshots';

    /**
     * Endpoint for SSH Keys
     *
     * @const string
     */
    public const SSH_KEYS_URL = self::API_URL . '/ssh-keys';

    /**
     * Endpoint for Startup Scripts
     *
     * @const string
     */
    public const STARTUP_SCRIPTS_URL = self::API_URL . '/startup-scripts';

    /**
     * Endpoint for Users
     *
     * @const string
     */
    public const USERS_URL = self::API_URL . '/users';

    /**
     * API Token
     *
     * @var string
     */
    protected $p_token;

    /**
     * Log Directory
     *
     * @var string
     */
    protected $p_log_location;

    /**
     * Log Reference
     *
     * @var string
     */
    protected $p_log;

    /**
     * Log Name
     *
     * @var string
     */
    protected $p_log_name;

    /**
     * Log File Tag
     *
     * @var string
     */
    protected $p_log_tag = "vultr";

    /**
     * Log Types
     *
     * @var array
     */
    protected $log_literals = [
        "debug",
        "info",
        "notice",
        "warning",
        "critical",
        "error"
    ];

    /**
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * Default constructor
     */
    public function __construct($token, array $attributes = [], Guzzle $guzzle = null)
    {
        $this->p_token = $token;
        if (isset($attributes['log_dir']) && is_dir($attributes['log_dir'])) {
            $this->p_log_location = $attributes['log_dir'];
        } else {
            $this->p_log_location = sys_get_temp_dir();
        }

        if (isset($attributes['log_name'])) {
            $this->p_log_name = $attributes['log_name'];
            if (!preg_match("/\.log$/", $this->p_log_name)) {
                $this->p_log_name .= ".log";
            }
        } else {
            $this->p_log_name = $this->pGenRandomString() . "." . time() . ".log";
        }
        if (isset($attributes['log_tag'])) {
            $this->p_log = new Logger($attributes['log_tag']);
        } else {
            $this->p_log = new Logger($this->p_log_tag);
        }

        if (isset($attributes['log_level']) && in_array($attributes['log_level'], $this->log_literals)) {
            if ($attributes['log_level'] == "debug") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::DEBUG));
            } elseif ($attributes['log_level'] == "info") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::INFO));
            } elseif ($attributes['log_level'] == "notice") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::NOTICE));
            } elseif ($attributes['log_level'] == "warning") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::WARNING));
            } elseif ($attributes['log_level'] == "error") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::ERROR));
            } elseif ($attributes['log_level'] == "critical") {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::CRITICAL));
            } else {
                $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::WARNING));
            }
        } else {
            $this->p_log->pushHandler(new StreamHandler($this->pGetLogPath(), Logger::INFO));
        }
        $this->guzzle = $guzzle ?: new Guzzle();
    }

    /**
     * getLogLocation
     * Alias to Get Log Path
     *
     *
     * @return string
     *
     */
    public function getLogLocation()
    {
        return $this->pGetLogPath();
    }

    /**
     * getAPIToken
     * Returns the stored API Token
     *
     *
     * @return string
     *
     */
    private function getAPIToken()
    {
        return $this->p_token;
    }

    /**
     * account
     * Pointer to the \Account class
     *
     *
     * @return object
     *
     */
    public function account()
    {
        $account = new Account($this);
        return $account;
    }

    /**
     * applications
     * Pointer to the \Applications class
     *
     *
     * @return object
     *
     */
    public function applications()
    {
        $ap = new Applications($this);
        return $ap;
    }

    /**
     * backups
     * Pointer to the \Backups class
     *
     *
     * @return object
     *
     */
    public function backups()
    {
        $bu = new Backups($this);
        return $bu;
    }

    /**
     * bareMetal
     * Pointer to the \BareMetal class
     *
     *
     * @return object
     *
     */
    public function bareMetal()
    {
        $bm = new BareMetal($this);
        return $bm;
    }

    /**
     * blockStorage
     * Pointer to the \BlockStorage class
     *
     *
     * @return object
     *
     */
    public function blockStorage()
    {
        $bs = new BlockStorage($this);
        return $bs;
    }

    /**
     * dns
     * Pointer to the \DNS class
     *
     *
     * @return object
     *
     */
    public function dns()
    {
        $dns = new DNS($this);
        return $dns;
    }

    /**
     * firewalls
     * Pointer to the \Firewalls class
     *
     *
     * @return object
     *
     */
    public function firewalls()
    {
        $fw = new Firewalls($this);
        return $fw;
    }

    /**
     * instances
     * Pointer to the \Instances class
     *
     *
     * @return object
     *
     */
    public function instances()
    {
        $instances = new Instances($this);
        return $instances;
    }

    /**
     * iso
     * Pointer to the \ISO class
     *
     *
     * @return object
     *
     */
    public function iso()
    {
        $iso = new ISO($this);
        return $iso;
    }

    /**
     * loadBalancers
     * Pointer to the \LoadBalancers class
     *
     *
     * @return object
     *
     */
    public function loadBalancers()
    {
        $lb = new LoadBalancers($this);
        return $lb;
    }

    /**
     * objectStorage
     * Pointer to the \ObjectStorage cass
     *
     *
     * @return object
     *
     */
    public function objectStorage()
    {
        $os = new ObjectStorage($this);
        return $os;
    }

    /**
     * operatingSystems
     * Pointer to the \OperatingSystems class
     *
     *
     * @return object
     *
     */
    public function operatingSystems()
    {
        $os = new OperatingSystems($this);
        return $os;
    }

    /**
     * plans
     * Pointer to the \Plans class
     *
     *
     * @return object
     *
     */
    public function plans()
    {
        $plans = new Plans($this);
        return $plans;
    }

    /**
     * privateNetworks
     * Pointer to the \PrivateNetworks class
     *
     *
     * @return object
     *
     */
    public function privateNetworks()
    {
        $pn = new PrivateNetworks($this);
        return $pn;
    }

    /**
     * regions
     * Pointer to the \Regions class
     *
     *
     * @return object
     *
     */
    public function regions()
    {
        $regions = new Regions($this);
        return $regions;
    }

    /**
     * reservedIPs
     * Pointer to the \ReservedIPs class
     *
     *
     * @return object
     *
     */
    public function reservedIPs()
    {
        $rip = new ReservedIPs($this);
        return $rip;
    }

    /**
     * snapshots
     * Pointer to the \Snapshots class
     *
     *
     * @return object
     *
     */
    public function snapshots()
    {
        $snap = new Snapshots($this);
        return $snap;
    }

    /**
     * instances
     * Pointer to the \SSHKeys class
     *
     *
     * @return object
     *
     */
    public function sshKeys()
    {
        $ssh = new SSHKeys($this);
        return $ssh;
    }

    /**
     * startupScripts
     * Pointer to the \StartupScripts class
     *
     *
     * @return object
     *
     */
    public function startupScripts()
    {
        $ss = new StartupScripts($this);
        return $ss;
    }

    /**
     * users
     * Pointer to the \Users class
     *
     *
     * @return object
     *
     */
    public function users()
    {
        $users = new Users($this);
        return $users;
    }


    /**
     * getLogPointer
     * Returns a referencd to the logger
     *
     *
     * @return object
     *
     */
    public function getLogPointer()
    {
        return $this->p_log;
    }

    /**
     * pGetLogPath
     * Returns full path and name of the log file
     *
     *
     * @return string
     *
     */
    protected function pGetLogPath()
    {
        return $this->p_log_location . '/' . $this->p_log_name;
    }

    /**
     * setHeaders
     * Sets the headers using the API Token
     *
     *
     * @return array
     *
     */
    public function setHeaders()
    {
        $array = [
            'User-Agent' => 'php-api-dutchie027/' . self::LIBRARY_VERSION,
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAPIToken()
        ];
        return $array;
    }

    /**
     * pGenRandomString
     * Generates a random string of $length
     *
     * @param int $length
     *
     * @return string
     *
     */
    public function pGenRandomString($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * makeAPICall
     * Makes the API Call
     *
     * @param $type string GET|POST|DELETE|PATCH
     * @param $url string endpoint
     * @param $body string - usually passed as JSON
     *
     * @return string Body Object
     * @throws string error message
     *
     */
    public function makeAPICall($type, $url, $body = null)
    {
        $data['headers'] = $this->setHeaders();
        $data['body'] = $body;
        try {
            $request = $this->guzzle->request($type, $url, $data);
            return $request->getBody();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $ja = json_decode($response->getBody()->getContents(), true);
                print $ja['error'];
            }
        }
    }
}
