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

class DNS
{
    /**
     * Reference to \API object
     *
     * @var API
     */
    protected $api;

    /**
     * Default value for DNS Sec on new domains
     *
     * @var string
     */
    private $d_dns_sec = 'disabled';

    /**
     * Default value for TTL
     *
     * @var int
     */
    private $d_ttl = 300;

    /**
     * Valid values for DNS Sec Settings
     *
     * @var array<string>
     */
    private $valid_dnssec = [
        'enabled',
        'disabled',
    ];

    /**
     * Valid Values for New Record Types
     *
     * @var array<string>
     */
    private $valid_types = [
        'A',
        'AAAA',
        'CNAME',
        'NS',
        'MX',
        'SRV',
        'TXT',
        'CAA',
        'SSHFP',
    ];

    /**
     * Records which require a priority setting
     *
     * @var array<string>
     */
    private $priority_records = [
        'MX',
        'SRV',
    ];

    /**
     * __construct
     * Takes reference from \API
     */
    public function __construct(API $api)
    {
        $this->api = $api;
    }

    /**
     * listDomains
     * Lists All Domains
     */
    public function listDomains(): string
    {
        return $this->api->makeAPICall('GET', $this->api::DNS_URL);
    }

    /**
     * getDomain
     * Get Info On A Domain
     */
    public function getDomain(string $domain): string
    {
        $this->validateDomain($domain);

        return $this->api->makeAPICall('GET', $this->api::DNS_URL . '/' . $domain);
    }

    /**
     * getSOA
     * Get SOA For A Domain
     */
    public function getSOA(string $domain): string
    {
        $this->validateDomain($domain);

        return $this->api->makeAPICall('GET', $this->api::DNS_URL . '/' . $domain . '/soa');
    }

    /**
     * getDNSSec
     * Get DNS Sec Settings
     */
    public function getDNSSec(string $domain): string
    {
        $this->validateDomain($domain);

        return $this->api->makeAPICall('GET', $this->api::DNS_URL . '/' . $domain . '/dnssec');
    }

    /**
     * deleteDomain
     * Delete A Domain
     */
    public function deleteDomain(string $domain): string
    {
        $this->validateDomain($domain);

        return $this->api->makeAPICall('DELETE', $this->api::DNS_URL . '/' . $domain);
    }

    /**
     * listRecords
     * List DNS Records of a specific domain
     */
    public function listRecords(string $domain): string
    {
        $this->validateDomain($domain);

        return $this->api->makeAPICall('GET', $this->api::DNS_URL . '/' . $domain . '/records');
    }

    /**
     * getRecord
     * Get a Specific DNS Record
     * @param array<string,string> $oa
     */
    public function getRecord(array $oa): string
    {
        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain Not Set');
        }
        $this->validateDomain($oa['domain']);

        if (!isset($oa['id'])) {
            throw new InvalidParameterException('ID not set');
        }
        $url = $this->api::DNS_URL . '/' . $oa['domain'] . '/records/' . $oa['id'];

        return $this->api->makeAPICall('GET', $url);
    }

    /**
     * deleteRecord
     * Delete a Specific DNS Record
     * @param array<string,string> $oa
     */
    public function deleteRecord(array $oa): string
    {
        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain Not Set');
        }
        $this->validateDomain($oa['domain']);

        if (!isset($oa['id'])) {
            throw new InvalidParameterException('ID not set');
        }
        $url = $this->api::DNS_URL . '/' . $oa['domain'] . '/records/' . $oa['id'];

        return $this->api->makeAPICall('DELETE', $url);
    }

    /**
     * updateDomain
     * Update A Domain
     * @param array<string,string> $oa
     */
    public function updateDomain(array $oa): string
    {
        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain Not Set');
        }
        $this->validateDomain($oa['domain']);

        if (!isset($oa['dns_sec'])) {
            throw new InvalidParameterException('dns_sec not set');
        }

        if (!in_array($oa['dns_sec'], $this->valid_dnssec, true)) {
            throw new InvalidParameterException("dns_sec must be enabled/disabled. It's not one of those");
        }
        $ba['dns_sec'] = $oa['dns_sec'];
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('PUT', $this->api::DNS_URL . '/' . $oa['domain'], $body);
    }

    /**
     * updateSOA
     * Update An SOA
     * @param array<string,string> $oa
     */
    public function updateSOA(array $oa): string
    {
        $execute = false;
        $ba = [];

        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain Not Set');
        }
        $this->validateDomain($oa['domain']);
        $url = $this->api::DNS_URL . '/' . $oa['domain'] . '/soa';

        if (isset($oa['email'])) {
            if (!filter_var($oa['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidParameterException('Email is invalid');
            }
            $execute = true;
            $ba['email'] = $oa['email'];
        }

        if (isset($oa['nsprimary'])) {
            if (!filter_var($oa['nsprimary'], FILTER_VALIDATE_DOMAIN)) {
                throw new InvalidParameterException('NS Primary Is Invalid');
            }
            $execute = true;
            $ba['nsprimary'] = $oa['nsprimary'];
        }

        if ($execute) {
            $body = $this->api->returnJSONBody($ba);

            return $this->api->makeAPICall('PATCH', $url, $body);
        }

        throw new VultrAPIException('Nothing to execute on line ' . __LINE__);
    }

    /**
     * createDomain
     * Create New DNS Domain
     * @param array<string,string> $oa
     */
    public function createDomain(array $oa): string
    {
        $ba['dns_sec'] = $this->d_dns_sec;

        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain is not set');
        }
        $this->validateDomain($oa['domain']);
        $ba['domain'] = $oa['domain'];

        if (isset($oa['ip']) && !filter_var($oa['ip'], FILTER_VALIDATE_IP)) {
            throw new InvalidParameterException('IP is set but is invalid');
        }

        if (isset($oa['ip']) && filter_var($oa['ip'], FILTER_VALIDATE_IP)) {
            $ba['ip'] = $oa['ip'];
        }

        if (isset($oa['dns_sec']) && !in_array($oa['dns_sec'], $this->valid_dnssec, true)) {
            throw new InvalidParameterException('DNS SEC is set but is not a valud option (enabled/disabled)');
        }

        if (isset($oa['dns_sec'])) {
            $ba['dns_sec'] = $oa['dns_sec'];
        }
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $this->api::DNS_URL, $body);
    }

    /**
     * createRecord
     * Create New DNS Record
     * @param array<string,string> $oa
     */
    public function createRecord(array $oa): string
    {
        $ba['ttl'] = $this->d_ttl;

        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain is not set');
        }
        $this->validateDomain($oa['domain']);
        $url = $this->api::DNS_URL . '/' . $oa['domain'] . '/records';

        if (!isset($oa['type'])) {
            throw new InvalidParameterException('Type is required');
        }

        if (!in_array($oa['type'], $this->valid_types, true)) {
            throw new InvalidParameterException('Invalid Type');
        }

        if (in_array($oa['type'], $this->priority_records, true)) {
            if (!isset($oa['priority']) || !is_numeric($oa['priority'])) {
                throw new InvalidParameterException('Priority must be set and be numeric if you use the type you used');
            }
            $ba['priority'] = $oa['priority'];
        }

        if (!isset($oa['data'])) {
            throw new InvalidParameterException('Data is required');
        }

        if (!isset($oa['name'])) {
            throw new InvalidParameterException('Name is required');
        }

        if ($oa['type'] == 'A' && !filter_var($oa['data'], FILTER_VALIDATE_IP)) {
            throw new InvalidParameterException('Type is A but data is not a valid IP');
        }

        if (isset($oa['ttl'])) {
            if (!is_numeric($oa['ttl'])) {
                throw new InvalidParameterException('TTL must be numeric');
            }
            $ba['ttl'] = $oa['ttl'];
        }
        $ba['name'] = $oa['name'];
        $ba['type'] = $oa['type'];
        $ba['data'] = $oa['data'];
        $body = $this->api->returnJSONBody($ba);

        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * updateRecord
     * Update A DNS Record
     * @param array<string,string> $oa
     */
    public function updateRecord(array $oa): string
    {
        $exe = false;
        $ba = [];

        if (!isset($oa['domain'])) {
            throw new InvalidParameterException('Domain is not set');
        }

        if (!isset($oa['id'])) {
            throw new InvalidParameterException('ID is not set');
        }
        $this->validateDomain($oa['domain']);
        $url = $this->api::DNS_URL . '/' . $oa['domain'] . '/records/' . $oa['id'];

        if (isset($oa['data'])) {
            $exe = true;
            $ba['data'] = $oa['data'];
        }

        if (isset($oa['name'])) {
            $exe = true;
            $ba['name'] = $oa['name'];
        }

        if (isset($oa['ttl'])) {
            if (!is_numeric($oa['ttl'])) {
                throw new InvalidParameterException('TTL must be numeric');
            }
            $ba['ttl'] = $oa['ttl'];
            $exe = true;
        }

        if (isset($oa['priority'])) {
            if (!is_numeric($oa['priority'])) {
                throw new InvalidParameterException('Priority must be numeric');
            }
            $ba['priority'] = $oa['priority'];
            $exe = true;
        }

        if ($exe) {
            $body = $this->api->returnJSONBody($ba);

            return $this->api->makeAPICall('PATCH', $url, $body);
        }

        throw new VultrAPIException('Nothing to execute on line ' . __LINE__);
    }

    private function validateDomain(string $domain): bool
    {
        if (!preg_match("/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/", $domain)) {
            throw new InvalidParameterException('Domain is not valid');
        }

        return true;
    }
}
