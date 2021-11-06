<?php

namespace App\Http\Helper;

abstract class SystemHelper
{
    public const MAX_EXECUTION_TIME_LIMIT = 300; // 5 minutes

    public static function allowLongerExecutionTimeLimit(?int $time = null): void
    {
        set_time_limit($time ?? self::MAX_EXECUTION_TIME_LIMIT);
    }
}
