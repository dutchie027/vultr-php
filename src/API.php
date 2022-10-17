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

use dutchie027\Vultr\Config\Config;
use dutchie027\Vultr\Exceptions\VultrAPIRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
     * The Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * The Guzzle HTTP client instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * Default constructor
     */
    public function __construct(string $configLoc = null)
    {
        $this->config = null === $configLoc ? new Config() : new Config($configLoc);

        $this->guzzle = new Client();
    }

    /**
     * account
     * Pointer to the \Account class
     */
    public function account(): Account
    {
        return new Account($this);
    }

    /**
     * applications
     * Pointer to the \Applications class
     */
    public function applications(): Applications
    {
        return new Applications($this);
    }

    /**
     * backups
     * Pointer to the \Backups class
     */
    public function backups(): Backups
    {
        return new Backups($this);
    }

    /**
     * bareMetal
     * Pointer to the \BareMetal class
     */
    public function bareMetal(): BareMetal
    {
        return new BareMetal($this);
    }

    /**
     * blockStorage
     * Pointer to the \BlockStorage class
     */
    public function blockStorage(): BlockStorage
    {
        return new BlockStorage($this);
    }

    /**
     * dns
     * Pointer to the \DNS class
     */
    public function dns(): DNS
    {
        return new DNS($this);
    }

    /**
     * firewalls
     * Pointer to the \Firewalls class
     */
    public function firewalls(): Firewalls
    {
        return new Firewalls($this);
    }

    /**
     * instances
     * Pointer to the \Instances class
     */
    public function instances(): Instances
    {
        return new Instances($this);
    }

    /**
     * iso
     * Pointer to the \ISO class
     */
    public function iso(): ISO
    {
        return new ISO($this);
    }

    /**
     * loadBalancers
     * Pointer to the \LoadBalancers class
     */
    public function loadBalancers(): LoadBalancers
    {
        return new LoadBalancers($this);
    }

    /**
     * objectStorage
     * Pointer to the \ObjectStorage cass
     */
    public function objectStorage(): ObjectStorage
    {
        return new ObjectStorage($this);
    }

    /**
     * operatingSystems
     * Pointer to the \OperatingSystems class
     */
    public function operatingSystems(): OperatingSystems
    {
        return new OperatingSystems($this);
    }

    /**
     * plans
     * Pointer to the \Plans class
     */
    public function plans(): Plans
    {
        return new Plans($this);
    }

    /**
     * privateNetworks
     * Pointer to the \PrivateNetworks class
     */
    public function privateNetworks(): PrivateNetworks
    {
        return new PrivateNetworks($this);
    }

    /**
     * regions
     * Pointer to the \Regions class
     */
    public function regions(): Regions
    {
        return new Regions($this);
    }

    /**
     * reservedIPs
     * Pointer to the \ReservedIPs class
     */
    public function reservedIPs(): ReservedIPs
    {
        return new ReservedIPs($this);
    }

    /**
     * snapshots
     * Pointer to the \Snapshots class
     */
    public function snapshots(): Snapshots
    {
        return new Snapshots($this);
    }

    /**
     * instances
     * Pointer to the \SSHKeys class
     */
    public function sshKeys(): SSHKeys
    {
        return new SSHKeys($this);
    }

    /**
     * startupScripts
     * Pointer to the \StartupScripts class
     */
    public function startupScripts(): StartupScripts
    {
        return new StartupScripts($this);
    }

    /**
     * users
     * Pointer to the \Users class
     */
    public function users(): Users
    {
        return new Users($this);
    }

    /**
     * setHeaders
     * Sets the headers using the API Token
     *
     * @return array<string,string>
     */
    public function setHeaders(): array
    {
        return [
            'User-Agent' => 'php-api-dutchie027/' . self::LIBRARY_VERSION,
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->config->getToken(),
        ];
    }

    /**
     * pGenRandomString
     * Generates a random string of $length
     */
    public function pGenRandomString(int $length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * makeAPICall
     * Makes the API Call
     */
    public function makeAPICall(string $type, string $url, string $body = null): string
    {
        $data['headers'] = $this->setHeaders();
        $data['body'] = $body;

        try {
            $request = $this->guzzle->request($type, $url, $data);

            return $request->getBody()->getContents();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $httpCode = $e->getCode();

                throw new VultrAPIRequestException('A HTTP Code ' . $httpCode . ' was thrown when calling $url');
            }

            throw new VultrAPIRequestException(('An unknown error ocurred while performing the request to ' . $url));
        }
    }

    /**
     * returns a JSON body from a passed array
     * @param array<mixed> $json
     */
    public function returnJSONBody(array $json): string
    {
        return json_encode($json) ?: '';
    }
}
