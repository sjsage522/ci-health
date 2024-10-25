<?php

namespace Mailplug\Health\Check;

use Laminas\Diagnostics\Check\Callback;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\DiskUsage;
use Laminas\Diagnostics\Check\OpCacheMemory;
use Laminas\Diagnostics\Check\PDOCheck;
use Laminas\Diagnostics\Check\PhpVersion;
use Laminas\Diagnostics\Check\RabbitMQ;
use Laminas\Diagnostics\Check\Redis;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Mailplug\Health\Config\Config;

class HealthCheckService
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;   
    }

    public function healthCheckAll(): array
    {
        $healthCheckLists = HealthCheckTarget::values();

        $checkList = [];
        foreach ($healthCheckLists as $healthCheckTarget) {
            if (in_array($healthCheckTarget, $this->config->excludes)) {
                continue;
            }

            $check = $this->healthCheck($healthCheckTarget);
            if ($check === null) {
                continue;
            }
            
            $checkList[$healthCheckTarget->getValue()] = $check;
        }

        return $checkList;
    }

    private function healthCheck(HealthCheckTarget $healthCheckTarget): ?array
    {
        switch ($healthCheckTarget) {
            case HealthCheckTarget::RABBITMQ:
                return $this->rabbitMQCheck();
            case HealthCheckTarget::REDIS:
                return $this->redisCheck();
            case HealthCheckTarget::DB:
                return $this->dbCheck();
            case HealthCheckTarget::DISK_USAGE:
                return $this->diskUsageCheck();
            case HealthCheckTarget::PHP:
                return $this->phpCheck();
            default:
                return null;
        }
    }

    private function rabbitMQCheck(): array
    {
        return $this->getCheckResult(new RabbitMQ($this->config->rabbitMqHost, $this->config->rabbitMqPort, $this->config->rabbitMqUser, $this->config->rabbitMqPassword, $this->config->rabbitMqVhost));
    }

    private function redisCheck(): array
    {
        return $this->getCheckResult(new Redis($this->config->redisHost, $this->config->redisPort, $this->config->redisPassword == '' ? null : $this->config->redisPassword));
    }

    private function dbCheck(): array
    {
        return [
            HealthCheckTarget::MYSQLDB => $this->pdoCheck('mysql:host=' . $this->config->mysqlHost . ';' . 'dbname=' . $this->config->mysqlDatabase,  $this->config->mysqlUser, $this->config->mysqlPassword),
            HealthCheckTarget::POSTGRESDB => $this->pdoCheck('pgsql:host=' . $this->config->psqlHost . ';' . 'dbname=' . $this->config->psqlDatabase,  $this->config->psqlUser, $this->config->psqlPassword),
        ];
    }

    private function diskUsageCheck(): array
    {
        return $this->getCheckResult(new DiskUsage(70, 100));
    }


    private function pdoCheck(string $dsn, string $username, string $password): array
    {
        return $this->getCheckResult(new PDOCheck($dsn, $username, $password));
    }

    private function phpCheck(): array
    {
        $phpfpmCountCheck = new Callback(function () {
            exec('ps -aux | grep php-fpm | grep -v grep | wc -l', $output, $return);

            if ($return > 0) {
                return new Failure('php-fpm is not running');
            }

            return new Success("php-fpm processes are running. [$output[0]]");
        });

        return [
            HealthCheckTarget::PHP_VERSION => $this->getCheckResult(new PhpVersion('7.4', '>=')),
            HealthCheckTarget::PHPFPM_COUNT => $this->getCheckResult($phpfpmCountCheck),
            HealthCheckTarget::OPCACHE_MEMORY => $this->getCheckResult(new OpCacheMemory(70, 90)),
        ];
    }

    private function getCheckResult(CheckInterface $checker): array
    {
        try {
            $checkResult = $checker->check();
        } catch (\Exception $e) {
            $checkResult = new Failure($e->getMessage());
        }

        return (new CheckResultWrapper($checkResult))
            ->toArray();
    }
}
