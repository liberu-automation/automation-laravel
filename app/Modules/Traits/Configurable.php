<?php

namespace App\Modules\Traits;

trait Configurable
{
    protected array $config = [];

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    public function setConfigValue(string $key, mixed $value): void
    {
        data_set($this->config, $key, $value);
    }

    public function mergeConfig(array $config): void
    {
        $this->config = array_merge_recursive($this->config, $config);
    }
}
