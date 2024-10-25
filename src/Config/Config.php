<?php

namespace Mailplug\Health\Config;

/**
 * Configuration class for the health check
 * 
 * change the values of the properties to match your environment
 */
class Config
{
    /**
     * @var array List of components that should be alive
     */
    public array $alives = [];

    /**
     * @var array<HealthCheckTarget> List of components that should be excluded from the health check
     */
    public array $excludes = [];

    /**
     * rabbit mq config
     */
    public string $rabbitMqHost = 'localhost';

    public int $rabbitMqPort = 5672;

    public string $rabbitMqUser = '';

    public string $rabbitMqPassword = '';

    public string $rabbitMqVhost = '/';
    /** */

    /**
     * redis config
     */
    public string $redisHost = 'localhost';

    public int $redisPort = 6379;

    public string $redisPassword = '';
    /** */

    /**
     * db config
     */
    public string $mysqlHost = 'localhost';

    public string $mysqlDatabase = '';

    public string $mysqlUser = '';

    public string $mysqlPassword = '';

    public string $psqlHost = 'localhost';

    public string $psqlDatabase = '';

    public string $psqlUser = '';

    public string $psqlPassword = '';
    /** */

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
