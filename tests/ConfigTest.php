<?php

declare(strict_types=1);

namespace dutchie027\Test\Vultr;

use dutchie027\Vultr\Config\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $tmp_ini;

    protected function setUp(): void
    {
        $this->tmp_ini = tempnam(sys_get_temp_dir(), 'phpunit') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php-unit';
        $handle = fopen($this->tmp_ini, 'w');
        $tempDir = sys_get_temp_dir();

        if ($handle) {
            fwrite($handle, '[api]' . PHP_EOL);
            fwrite($handle, 'TOKEN="mysecrettoken"' . PHP_EOL);
            fwrite($handle, '[log]' . PHP_EOL);
            fwrite($handle, 'LOG_PREFIX="vultr"' . PHP_EOL);
            fwrite($handle, 'LOG_LEVEL=100' . PHP_EOL);
            fwrite($handle, 'LOG_DIR="' . $tempDir . '"' . PHP_EOL);
            fclose($handle);
        }

        $this->config = new Config($this->tmp_ini);
    }

    public function testgetToken(): void
    {
        self::assertEquals('mysecrettoken', $this->config->getToken());
    }

    public function testgetLogDir(): void
    {
        self::assertEquals(sys_get_temp_dir(), Config::getLogDir());
    }

    public function testgetLogLevel(): void
    {
        self::assertEquals(100, Config::getLogLevel());
    }

    public function testgetLogPrefix(): void
    {
        self::assertEquals('vultr', Config::getLogPrefix());
    }
}
