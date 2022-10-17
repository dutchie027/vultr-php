<?php

declare(strict_types=1);

namespace dutchie027\Vultr\Init;

use Composer\Script\Event;

class Setup
{
    /**
     * Below we use @phpstan-ignore-next-line in order to ignore two errors that
     * PHPSTAN will throw. It thinks the constnats aren't used, when in actuality they
     * are. They're called dynamically via the KVP_SECTIONS constant in a loop to create
     * each section's respective KVP in the default .ini file.
     */

    /** @phpstan-ignore-next-line */
    private const API_KVPS = [
        'TOKEN',
    ];

    /** @phpstan-ignore-next-line */
    private const LOG_KVPS = [
        'LOG_PREFIX',
        'LOG_LEVEL',
        'LOG_DIR',
    ];

    private const KVP_SECTIONS = [
        'api',
        'log',
    ];

    /**
     * @var string
     */
    private static $iniFile;

    public static function generateBlankIni(Event $event): void
    {
        $config = $event->getComposer()->getConfig()->get('vendor-dir');
        $envFile = dirname($config) . DIRECTORY_SEPARATOR . 'vultr.ini';
        $myfile = fopen($envFile, 'w') or die('Unable to open file!');

        foreach (self::KVP_SECTIONS as $key) {
            $header = '[' . $key . ']' . PHP_EOL;
            fwrite($myfile, $header);

            foreach (constant('self::' . strtoupper($key) . '_KVPS') as $kvp) {
                $line = $kvp . '=' . PHP_EOL;
                fwrite($myfile, $line);
            }
            fwrite($myfile, PHP_EOL);
        }
        fclose($myfile);
        self::$iniFile = $envFile;
    }

    /**
     * Method to return the location of the ini file
     */
    public static function getFileLocation(): string
    {
        return self::$iniFile;
    }
}
