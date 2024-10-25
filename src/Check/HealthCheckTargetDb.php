<?php

namespace Mailplug\Health\Check;

class HealthCheckTargetDb extends HealthCheckTarget
{
    const MYSQLDB = 'mysqldb';

    const POSTGRESDB = 'postgresdb';

    const REDIS = 'redis';
}
