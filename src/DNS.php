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

class DNS
{

    /**
     * Reference to \API object
     *
     * @var object
     */
    protected $api;

    /**
     * Default value for DNS Sec on new domains
     *
     * @var string
     */
    private $d_dns_sec = "disabled";

    /**
     * Default value for TTL
     *
     * @var string
     */
    private $d_ttl = 300;

    /**
     * Valid values for DNS Sec Settings
     *
     * @var array
     */
    private $valid_dnssec = [
        "enabled",
        "disabled",
    ];

    /**
     * Valid Values for New Record Types
     *
     * @var array
     */
    private $valid_types = [
        "A",
        "AAAA",
        "CNAME",
        "NS",
        "MX",
        "SRV",
        "TXT",
        "CAA",
        "SSHFP",
    ];

    /**
     * Records which require a priority setting
     *
     * @var array
     */
    private $priority_records = [
        "MX",
        "SRV",
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
    }

    /**
     * listDomains
     * Lists All Domains
     *
     *
     * @return string
     *
     */
    public function listDomains()
    {
        return $this->api->makeAPICall('GET', $this->api::DNS_URL);
    }

    /**
     * getDomain
     * Get Info On A Domain
     *
     * @param string $domain
     *
     * @return string
     *
     */
    public function getDomain($domain)
    {
        $this->validateDomain($domain);
        return $this->api->makeAPICall('GET', $this->api::DNS_URL . "/" . $domain);
    }

    /**
     * getSOA
     * Get SOA For A Domain
     *
     * @param string $domain
     *
     * @return string
     *
     */
    public function getSOA($domain)
    {
        $this->validateDomain($domain);
        return $this->api->makeAPICall('GET', $this->api::DNS_URL . "/" . $domain . "/soa");
    }

    /**
     * getDNSSec
     * Get DNS Sec Settings
     *
     * @param string $domain
     *
     * @return string
     *
     */
    public function getDNSSec($domain)
    {
        $this->validateDomain($domain);
        return $this->api->makeAPICall('GET', $this->api::DNS_URL . "/" . $domain . "/dnssec");
    }

    /**
     * deleteDomain
     * Delete A Domain
     *
     * @param string $domain
     *
     * @return string
     *
     */
    public function deleteDomain($domain)
    {
        $this->validateDomain($domain);
        return $this->api->makeAPICall('DELETE', $this->api::DNS_URL . "/" . $domain);
    }

    /**
     * listRecords
     * List DNS Records of a specific domain
     *
     * @param string $domain
     *
     * @return string
     *
     */
    public function listRecords($domain)
    {
        $this->validateDomain($domain);
        return $this->api->makeAPICall('GET', $this->api::DNS_URL . "/" . $domain . "/records");
    }

    /**
     * getRecord
     * Get a Specific DNS Record
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function getRecord($oa)
    {
        if (!isset($oa['domain'])) {
            print "Domain Not Set";
        }
        $this->validateDomain($oa['domain']);
        if (!isset($oa['id'])) {
            print "id not set";
            exit;
        }
        $url = $this->api::DNS_URL . "/" . $oa['domain'] . "/records/" . $oa['id'];
        return $this->api->makeAPICall('GET', $url);
    }

    /**
     * deleteRecord
     * Delete a Specific DNS Record
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function deleteRecord($oa)
    {
        if (!isset($oa['domain'])) {
            print "Domain Not Set";
        }
        $this->validateDomain($oa['domain']);
        if (!isset($oa['id'])) {
            print "id not set";
            exit;
        }
        $url = $this->api::DNS_URL . "/" . $oa['domain'] . "/records/" . $oa['id'];
        return $this->api->makeAPICall('DELETE', $url);
    }

    /**
     * updateDomain
     * Update A Domain
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function updateDomain($oa)
    {
        if (!isset($oa['domain'])) {
            print "Domain Not Set";
        }
        $this->validateDomain($oa['domain']);
        if (!isset($oa['dns_sec'])) {
            print "dns_sec not set";
            exit;
        }
        if (!in_array($oa['dns_sec'], $this->valid_dnssec)) {
            print "dns_sec must be enabled/disabled. It's not one of those";
            exit;
        }
        $ba['dns_sec'] = $oa['dns_sec'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('PUT', $this->api::DNS_URL . "/" . $oa['domain'], $body);
    }

    /**
     * updateSOA
     * Update An SOA
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function updateSOA($oa)
    {
        $execute = false;
        if (!isset($oa['domain'])) {
            print "Domain Not Set";
        }
        $this->validateDomain($oa['domain']);
        $url = $this->api::DNS_URL . "/" . $oa['domain'] . "/soa";
        if (isset($oa['email'])) {
            if (!filter_var($oa['email'], FILTER_VALIDATE_EMAIL)) {
                print "Email is invalid";
                exit;
            } else {
                $execute = true;
                $ba['email'] = $oa['email'];
            }
        }

        if (isset($oa['nsprimary'])) {
            if (!filter_var($oa['nsprimary'], FILTER_VALIDATE_DOMAIN)) {
                print "NS Primary Is Invalid";
                exit;
            } else {
                $execute = true;
                $ba['nsprimary'] = $oa['nsprimary'];
            }
        }
        if ($execute) {
            $body = json_encode($ba);
            return $this->api->makeAPICall('PATCH', $url, $body);
        }
    }

    /**
     * createDomain
     * Create New DNS Domain
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function createDomain($oa)
    {
        $ba['dns_sec'] = $this->d_dns_sec;
        if (!isset($oa['domain'])) {
            print "Domain is not set";
            exit;
        }
        $this->validateDomain($oa['domain']);
        $ba['domain'] = $oa['domain'];
        if (isset($oa['ip']) && !filter_var($oa['ip'], FILTER_VALIDATE_IP)) {
            print "IP is set but is invalid";
            exit;
        } elseif (isset($oa['ip']) && filter_var($oa['ip'], FILTER_VALIDATE_IP)) {
            $ba['ip'] = $oa['ip'];
        }
        if (isset($oa['dns_sec']) && !in_array($oa['dns_sec'], $this->valid_dnssec)) {
            print "DNS SEC is set but is not a valud option (enabled/disabled)";
            exit;
        } elseif (isset($oa['dns_sec'])) {
            $ba['dns_sec'] = $oa['dns_sec'];
        }
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $this->api::DNS_URL, $body);
    }

    /**
     * createRecord
     * Create New DNS Record
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function createRecord($oa)
    {
        $ba['ttl'] = $this->d_ttl;
        if (!isset($oa['domain'])) {
            print "Domain is not set";
            exit;
        }
        $this->validateDomain($oa['domain']);
        $url = $this->api::DNS_URL . "/" . $oa['domain'] . "/records";
        if (!isset($oa['type'])) {
            print "Type is required";
            exit;
        }
        if (!in_array($oa['type'], $this->valid_types)) {
            print "Invalid Type";
            exit;
        }
        if (in_array($oa['type'], $this->priority_records)) {
            if (!isset($oa['priority']) || !is_numeric($oa['priority'])) {
                print "Priority must be set and be numeric if you use the type you used";
                exit;
            } else {
                $ba['priority'] = $oa['priority'];
            }
        }
        if (!isset($oa['data'])) {
            print "Data is required";
            exit;
        }
        if (!isset($oa['name'])) {
            print "Name is required";
            exit;
        }
        if ($oa['type'] == "A" && !filter_var($oa['data'], FILTER_VALIDATE_IP)) {
            print "Type is A but data is not a valid IP";
            exit;
        }
        if (isset($oa['ttl'])) {
            if (!is_numeric($oa['ttl'])) {
                print "TTL must be numeric";
                exit;
            }
            $ba['ttl'] = $oa['ttl'];
        }
        $ba['name'] = $oa['name'];
        $ba['type'] = $oa['type'];
        $ba['data'] = $oa['data'];
        $body = json_encode($ba);
        return $this->api->makeAPICall('POST', $url, $body);
    }

    /**
     * updateRecord
     * Update A DNS Record
     *
     * @param array $oa
     *
     * @return string
     *
     */
    public function updateRecord($oa)
    {
        $exe = false;
        if (!isset($oa['domain'])) {
            print "Domain is not set";
            exit;
        }
        if (!isset($oa['id'])) {
            print "ID is not set";
            exit;
        }
        $this->validateDomain($oa['domain']);
        $url = $this->api::DNS_URL . "/" . $oa['domain'] . "/records/" . $oa['id'];
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
                print "TTL must be numeric";
                exit;
            }
            $ba['ttl'] = $oa['ttl'];
            $exe = true;
        }
        if (isset($oa['priority'])) {
            if (!is_numeric($oa['priority'])) {
                print "priority must be numeric";
                exit;
            }
            $ba['priority'] = $oa['priority'];
            $exe = true;
        }
        if ($exe) {
            $body = json_encode($ba);
            return $this->api->makeAPICall('PATCH', $url, $body);
        }
    }

    private function validateDomain($domain)
    {
        if (!preg_match("/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/", $domain)) {
            print "Domain is not valid";
            exit;
        }
        return true;
    }
}
