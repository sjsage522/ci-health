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
        $healthCheckLists = HealthCheckTarget::mergeValues();

        $checkList = [];
        foreach ($healthCheckLists as $healthCheckTarget) {
            if (in_array($healthCheckTarget, $this->config->excludes)) {
                continue;
            }

            $check = $this->healthCheck($healthCheckTarget);
            if ($check === null) {
                continue;
            }

            if ($healthCheckTarget instanceof HealthCheckTargetDb) {
                $checkList['db'][$healthCheckTarget->getValue()] = $check;
            } else if ($healthCheckTarget instanceof HealthCheckTargetPhp) {
                $checkList['php'][$healthCheckTarget->getValue()] = $check;
            } else {
                $checkList[$healthCheckTarget->getValue()] = $check;
            }
        }

        return $checkList;
    }

    private function healthCheck(HealthCheckTarget $healthCheckTarget): ?array
    {
        switch ($healthCheckTarget) {
            case HealthCheckTargetEtc::RABBITMQ:
                return $this->rabbitMQCheck();
            case HealthCheckTargetDb::REDIS:
                return $this->redisCheck();
            case HealthCheckTargetDb::MYSQLDB:
                return $this->mysqlCheck();
            case HealthCheckTargetDb::POSTGRESDB:
                return $this->pgsqlCheck();
            case HealthCheckTargetEtc::DISK_USAGE:
                return $this->diskUsageCheck();
            case HealthCheckTargetPhp::PHPFPM_COUNT:
                return $this->phpFpmCountCheck();
            case HealthCheckTargetPhp::PHP_VERSION:
                return $this->phpVersionCheck();
            case HealthCheckTargetPhp::OPCACHE_MEMORY:
                return $this->phpOpcacheCheck();
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

    private function mysqlCheck(): array
    {
        return $this->pdoCheck('mysql:host=' . $this->config->mysqlHost . ';' . 'dbname=' . $this->config->mysqlDatabase,  $this->config->mysqlUser, $this->config->mysqlPassword);
    }

    private function pgsqlCheck(): array
    {
        return $this->pdoCheck('pgsql:host=' . $this->config->psqlHost . ';' . 'dbname=' . $this->config->psqlDatabase,  $this->config->psqlUser, $this->config->psqlPassword);
    }

    private function diskUsageCheck(): array
    {
        return $this->getCheckResult(new DiskUsage(70, 100));
    }


    private function pdoCheck(string $dsn, string $username, string $password): array
    {
        return $this->getCheckResult(new PDOCheck($dsn, $username, $password));
    }

    private function phpFpmCountCheck()
    {
        $phpfpmCountCheck = new Callback(function () {
            exec('ps -aux | grep php-fpm | grep -v grep | wc -l', $output, $return);

            if ($return > 0) {
                return new Failure('php-fpm is not running');
            }

            return new Success("php-fpm processes are running. [$output[0]]");
        });

        return $this->getCheckResult($phpfpmCountCheck);
    }

    private function phpOpcacheCheck()
    {
        return $this->getCheckResult(new OpCacheMemory(70, 90));
    }

    private function phpVersionCheck()
    {
        return $this->getCheckResult(new PhpVersion('7.4', '>='));
    }

    private function getCheckResult(CheckInterface $checker): array
    {
        try {
            $checkResult = $checker->check();
        } catch (\Exception $e) {
            $checkResult = new Failure($e->getMessage());
        }

        $resultWrapper = (new CheckResultWrapper($checkResult))
            ->toArray();
        
        if ($this->config->showDetails == 'never') {
            unset($resultWrapper['details']);
        }

        return $resultWrapper;
    }
}
