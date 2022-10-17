<?php

declare(strict_types=1);

namespace dutchie027\Vultr\Config;

class Config
{
    /**
     * 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'
     */
    private const ALLOWED_LEVELS = [100, 200, 250, 300, 400, 500, 550, 600];

    private static string $s_log_dir;

    private static int $s_log_level;

    private static string $s_log_prefix;

    private string $token;

    /**
     * @var array<string,array<string>>
     */
    private $ini_data;

    /**
     * Default Constructor - Initialize Values
     */
    public function __construct(string $loc = 'vultr.ini')
    {
        /** @phpstan-ignore-next-line */
        $file = is_file($loc) ? $loc : dirname(debug_backtrace()[0]['file']) . DIRECTORY_SEPARATOR . $loc;
        $this->ini_data = $this->returnIniArray($file);
        $this->token = $this->returnStringFromIni('api/TOKEN', 'token');
        self::$s_log_dir = $this->returnStringFromIni('log/LOG_DIR', sys_get_temp_dir());
        self::$s_log_prefix = $this->returnStringFromIni('log/LOG_PREFIX', 'vultr');
        self::$s_log_level = $this->returnIntFromIni('log/LOG_LEVEL', 100);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns Log Directory
     */
    public static function getLogDir(): string
    {
        return self::$s_log_dir;
    }

    /**
     * Returns Logging Level
     */
    public static function getLogLevel(): int
    {
        return self::$s_log_level;
    }

    /**
     * Returns Log Prefix
     */
    public static function getLogPrefix(): string
    {
        return self::$s_log_prefix;
    }

    /**
     * Checks existence of ini file and then returns KVP Array
     *
     * @param string $loc
     *
     * @return array<string,array<string>>
     */
    private function returnIniArray($loc): array
    {
        $return = [];

        if (file_exists($loc)) {
            $return = parse_ini_file($loc, true) ?: [];
        }

        return $return;
    }

    /**
     * Used to set values from .ini array or default value
     */
    private function returnStringFromIni(string $var, string $dv): string
    {
        [$root, $key] = explode('/', $var);

        return (isset($this->ini_data[$root][$key])) ? $this->ini_data[$root][$key] : $dv;
    }

    /**
     * Used to set values from .ini array or default value
     */
    private function returnIntFromIni(string $var, int $dv): int
    {
        [$root, $key] = explode('/', $var);

        return ((isset($this->ini_data[$root][$key])) && (in_array((int) $this->ini_data[$root][$key], self::ALLOWED_LEVELS, true))) ? (int) $this->ini_data[$root][$key] : $dv;
    }
}
