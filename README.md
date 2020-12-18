# vultr-php
PHP Library Intended to Interact with [Vultr's v2 API](https://www.vultr.com/api/v2)
There are a few other PHP libraries out that do similar things, but I wasn't happy that they were very monolithic and also they used cURL and not Guzzle. Further, there was no logging in any of the libraries, so I wrote this one.

## Installation
```php
composer require dutchie027/vultr
```

## Usage
```php
// require the composer library
require_once ('vendor/autoload.php');

//make the connction to the API for use
$api = new dutchie027\Vultr\API(VULTR_API_KEY);

...
```

## General Information

## Classes

### API

The main connection requires at minimum, an API key. You can get this by visiting [My Vultr Portal -> Account -> API](https://my.vultr.com/settings/#settingsapi). In the portal, make sure you set the IP(s) you'll be calling the API from also, as by default it will lock it to the single IP you request the API from.

Once you have the API token, you can simply connect with it or you can add options

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API(VULTR_API_KEY);

// Instantiate without defaults, this allows you to change things
// like log location, directory, the tag and possible future settings.
$settings = [
	'log_dir' => '/tmp',
	'log_name' => 'vultri',
	'log_tag' => 'vultr-api',
	'log_level' => 'error'
];

$api = new dutchie027\Vultr\API(VULTR_API_KEY, $settings);
```

#### Settings

The default settings are fine, however you might want to override the defaults or use your own.**NOTE: All settings are optional and you don't need to provide any**. 

Field | Type | Description | Default Value
----- | ---- | ----------- | -------------
`log_dir` | string | The directory where the log file is stored | [sys_get_temp_dir()](https://www.php.net/manual/en/function.sys-get-temp-dir.php)
`log_name` | string | The name of the log file that is created in `log_dir`. If you don't put .log at the end, it will append it | 6 random characters + [time()](https://www.php.net/manual/en/function.time.php) + .log 
`log_tag` | string | If you share this log file with other applications, this is the tag used in the log file | vultr
`log_level` | string | The level of logging the application will do. This must be either `debug`, `info`, `notice`, `warning`, `critical` or `error`. If it is not one of those values it will fail to the default | `warning`

### Account

Once you have a client, you can ask for the basic information about your account. NOTE: All payloads are returned in JSON, so you can choose how you want to deal with them:

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API(VULTR_API_KEY);

// Lets get the account info and what else this API key can do
print_r(json_decode($api->account()->getAccountInfo(), true));
```

### Block Storage

### Regions

For the most part, this is a support class, but if you want to use it you can.  Here's a few things you can do with it:

```php
// Ensure we have the composer libraries
require_once ('vendor/autoload.php');

// Instantiate with defaults
$api = new dutchie027\Vultr\API(VULTR_API_KEY);

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
* Clean up the code a bit more
* Stuff I'm obviously missing...

## Contributing

If you're having problems, spot a bug, or have a feature suggestion, [file an issue](https://github.com/dutchie027/vultr-php/issues). If you want, feel free to fork the package and make a pull request. This is a work in progresss as I get more info and further test the API.
