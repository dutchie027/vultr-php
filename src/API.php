<?php

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
    protected const LIBRARY_VERSION = '1.1.0';

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
     * Endpoint for Block Storage
     *
     * @const string
     */
    public const BLOCK_STORAGE_URL = self::API_URL . '/blocks';

    /**
     * Endpoint for Regions API
     *
     * @const string
     */
    public const REGIONS_URL = self::API_URL . '/regions';

    /**
     * Endpoint for Instances API
     *
     * @const string
     */
    public const INSTANCES_URL = self::API_URL . '/instances';

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
        $this->guzzle = $guzzle ? : new Guzzle();
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
    private function pGenRandomString($length = 6)
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
