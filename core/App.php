<?php
/**
 * आकाशवाणी — Premium Core Application Class
 * Clean Architecture Foundation
 */

namespace Aakashvani\Core;

class App
{
    private static ?App $instance = null;
    private array $config = [];
    private bool $debug = false;

    private function __construct()
    {
        $this->loadConfig();
    }

    public static function getInstance(): App
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $this->config = [
            'site_name' => 'आकाशवाणी',
            'site_tagline' => 'सूचनाको खुला आकाश',
            'site_url' => 'https://tankaadhikari.com.np',
            'timezone' => 'Asia/Kathmandu',
            'cache_ttl' => 300, // 5 minutes
            'api_timeout' => 10,
            'max_retries' => 3,
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function getTimezone(): string
    {
        return $this->config['timezone'];
    }

    public function now(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone($this->config['timezone']));
    }

    public function today(string $format = 'Y-m-d'): string
    {
        return $this->now()->format($format);
    }
}
