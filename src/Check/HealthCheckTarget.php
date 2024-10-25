<?php

namespace Mailplug\Health\Check;

use MyCLabs\Enum\Enum;

class HealthCheckTarget extends Enum {

    public static function mergeValues()
    {
        $values = [];
        foreach ([HealthCheckTargetDb::class, HealthCheckTargetEtc::class, HealthCheckTargetPhp::class] as $class) {
            $values = array_merge($values, $class::values());
        }

        return $values;
    }
}
