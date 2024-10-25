<?php

namespace Mailplug\Health\Client;

use Mailplug\Health\Check\HealthCheckService;
use Mailplug\Health\Config\Config;

class HealthCheckClient
{
    private HealthCheckService $service;

    private array $alives;

    public function __construct(Config $config)
    {
        $this->service = new HealthCheckService($config);
        $this->alives = $config->alives;
    }

    public function healthCheckAll(): Components
    {
        $checkList = $this->service->healthCheckAll();

        $status = 200;
        foreach ($checkList as $key => $value) {
            if ($this->isNotHealthy($key, $value)) {
                $status = 500;
                break;
            }
        }

        return new Components($status, $checkList);
    }

    /**
     * @return bool true if the component is not healthy
     */
    private function isNotHealthy(string $key, ?array $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (in_array($key, $this->alives) && isset($value['status']) && $value['status'] === 'failure') {
            return true;
        }

        foreach ($value as $subkey => $subValue) {
            if (is_array($subValue) && $this->isNotHealthy($subkey, $subValue)) {
                return true;
            }
        }

        return false;
    }
}
