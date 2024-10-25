<?php

namespace Mailplug\Health\Check;

class HealthCheckTargetPhp extends HealthCheckTarget
{
    const PHP_VERSION = 'php_version';

    const PHPFPM_COUNT = 'phpfpm_count';

    const OPCACHE_MEMORY = 'opcache_memory';
}
