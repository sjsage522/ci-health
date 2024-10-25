<?php

namespace Mailplug\Health\Check;

use MyCLabs\Enum\Enum;

class HealthCheckTarget extends Enum
{
    const RABBITMQ = 'rabbitmq';

    const REDIS = 'redis';

    const DISK_USAGE = 'disk_usage';

    //--------------------------------------------db enum group

    const DB = 'db';

    const MYSQLDB = 'mysqldb';

    const POSTGRESDB = 'postgresdb';

    //--------------------------------------------php enum group

    const PHP = 'php';

    const PHP_VERSION = 'php_version';

    const PHPFPM_COUNT = 'phpfpm_count';

    const OPCACHE_MEMORY = 'opcache_memory';
}
