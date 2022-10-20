# vultr-php

[![Latest Stable Version](https://poser.pugx.org/dutchie027/vultr/v)](//packagist.org/packages/dutchie027/vultr)
[![Total Downloads](https://poser.pugx.org/dutchie027/vultr/downloads)](//packagist.org/packages/dutchie027/vultr)
[![License](https://poser.pugx.org/dutchie027/vultr/license)](//packagist.org/packages/dutchie027/vultr)
[![CodeFactor](https://www.codefactor.io/repository/github/dutchie027/vultr-php/badge)](https://www.codefactor.io/repository/github/dutchie027/vultr-php)[![PHPStan Check](https://github.com/dutchie027/vultr-php/actions/workflows/standards.yml/badge.svg)](https://github.com/dutchie027/vultr-php/actions/workflows/standards.yml)

PHP Library Intended to Interact with [Vultr's v2 API](https://www.vultr.com/api/v2)

## Installation

```php
composer require dutchie027/vultr
```

## Usage

```php
// require the composer library
require_once ('vendor/autoload.php');

// make the connction to the API for use
// this will use "vultr.ini" with the KVPs created
// at composer load. As a minimum you need the API
$api = new dutchie027\Vultr\API();

...
```
### vultr.ini
This file will be created when you donload the library using composer. If you don't generate one at initial load/run time with composer
simply have the minimum Key/Value Pair in the `[api]` key with the `TOKEN` being your API token from the My Vultr Portal.

```ini
# minimum vultr.ini
[api]
TOKEN='8675309TOMMY30918IN'
```

## General Information

### Class Listing

The library has the following classes:

* [Account](/docs/Account.md)
* [API](/docs/API.md)
* [Backups](/docs/Backups.md)
* [BareMetal](/docs/BareMetal.md)
* [BlockStorage](/docs/BlockStorage.md)
* [DNS](/docs/DNS.md)
* [Firewalls](/docs/Firewalls.md)
* [Instances](/docs/Instances.md)
* [ISO](/docs/ISO.md)
* [LoadBalancers](/docs/LoadBalancers.md)
* [ObjectStorage](/docs/ObjectStorage.md)
* [OperatingSystems](/docs/OperatingSystems.md)
* [Plans](/docs/Plans.md)
* [PrivateNetworks](/docs/PrivateNetworks.md)
* [Regions](/docs/Regions.md)
* [ReservedIPs](/docs/ReservedIPs.md)
* [Snapshots](/docs/Snapshots.md)
* [SSHKeys](/docs/SSHKeys.md)
* [StartupScripts](/docs/StartupScripts.md)
* [Users](/docs/Users.md)

## Class Information

### API

The main connection requires at minimum, an API key. You can get this by visiting [My Vultr Portal -> Account -> API](https://my.vultr.com/settings/#settingsapi). In the portal, make sure you set the IP(s) you'll be calling the API from also, as by default it will lock it to the single IP you request the API from.

Once you have the API token, you can simply connect with it or you can add options

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();
```

### Account

Once you have a client, you can ask for the basic information about your account. NOTE: All payloads are returned in JSON, so you can choose how you want to deal with them:

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

// Lets get the account info and what else this API key can do
print_r(json_decode($api->account()->getAccountInfo(), true));
```

### Block Storage

#### Creating Block Storage

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

$config = [
    'region' => 'ewr',
    'size' => '10',
    'label' => 'my first storage',
];

$json_return = $api->blockStorage()->createBlockStorage($config);
```

##### Config

If you call this without any `$config` it will still create block storage. It will use the defaults as described below.

| Parameter | Type    | Description                                                                                                                                     | Default Value |
| --------- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ------------- |
| `region`  | string  | The region where you want the storage created. *NOTE* If you choose a location that does NOT have block storate, it will revert to the default. | ewr           |
| `size`    | integer | The size (in GB) of how much storage you want created. *NOTE* This value must be between 10 and 10000                                           | 10            |
| `label`   | string  | A text label to be associated with the storage                                                                                                  | _null_        |

##### Return Value

You will be returned with a JSON payload that includes the newly created Block ID as well as the cost (in dollars) and size (in GB):

```json
{
  "block": {
    "id": "8692c434-08fa-4efb-a0fb-966a338aee07",
    "date_created": "2020-12-18T03:11:57+00:00",
    "cost": 1,
    "status": "pending",
    "size_gb": 10,
    "region": "ewr",
    "attached_to_instance": "",
    "label": "my first storage"
  }
}
```

#### Updating Block Storage

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

$config = [
    'blockid' => '8692c434-08fa-4efb-a0fb-966a338aee07',
    'size' => '40',
    'label' => 'not my first rodeo',
];

$api->blockStorage()->updateBlockStorage($config);
```

##### Block Storage Config

Block storage can only be updated once every 60 seconds. To update the storage you need a minimum of the `blockid` and either a `size` or new `label`.

#### Deleting Block Storage

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

$api->blockStorage()->deleteBlockStorage($blockid);
```

The block ID is in the form of a GUID (something like 8692c434-08fa-4efb-a0fb-966a338aee07). If you provide a GUID that isn't in your storage container, it will fail.

#### Listing Specific Storage

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

$json_return = $api->blockStorage()->getBlockStorage($blockid);
```

The block ID is in the form of a GUID (something like 8692c434-08fa-4efb-a0fb-966a338aee07). If you provide a GUID that isn't in your storage container, it will fail.

#### Attaching Block Storage

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

$config = [
 'block_id' => '98772323-044a-4efb-a0fb-1234338abb07',
 'instance' => '12345434-08fa-4efb-a0fb-966a338aee07',
    'live' => false,
];

$api->blockStorage()->attachBlockStorage($config);
```

All three values are required in the `$config`. The `block_id` is the ID of the block storage you want to attach. The `instance` is the instance ID of the machine you want the storage attached to. It must also be in the same location as the storage. The value `live` is either `true` or `false`. If it is set to `true` it will attach the storage but NOT restart the instance. If you set `live` to `false` it will RESTART the instance and then attach the block storage.

#### Detatching Block Storage

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

$config = [
 'block_id' => '98772323-044a-4efb-a0fb-1234338abb07',
    'live' => false,
];

$api->blockStorage()->detatchBlockStorage($config);
```

Both values are required in the `$config`. The `block_id` is the ID of the block storage you want to detatch. The value `live` is either `true` or `false`. If it is set to `true` it will attach the storage but NOT restart the instance. If you set `live` to `false` it will RESTART the instance and then detatch the block storage.

### Regions

For the most part, this is a support class, but if you want to use it you can.  Here's a few things you can do with it:

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API();

// Print various information about the Regions. All pretty self-explanatory
$api->regions()->listIds();
$api->regions()->listCities();
$api->regions()->listCountries();
$api->regions()->listContinents();
$api->regions()->listNames();
```

## To-Do

* Bring in more of the function(s) from Vultr
* Document the class(es) with proper doc blocks better
* Move the documentation in to separate markdowns

## Contributing

If you're having problems, spot a bug, or have a feature suggestion, [file an issue](https://github.com/dutchie027/vultr-php/issues). If you want, feel free to fork the package and make a pull request. This is a work in progresss as I get more info and further test the API.
