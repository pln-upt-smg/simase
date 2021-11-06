<?php

namespace App\Http\Helper;

abstract class SystemHelper
{
    public const DEFAULT_EXECUTION_TIME_LIMIT = 30; // 30 seconds
    public const MAX_EXECUTION_TIME_LIMIT = 300;    // 5 minutes

    /**
     * Allow the script to have longer execution time limit.
     * Please use the reset execution time limit to reset the PHP Execution Time Limit
     * back to it's normal value (30 seconds).
     *
     * @param int|null $time
     */
    public static function allowLongerExecutionTimeLimit(?int $time = null): void
    {
        set_time_limit($time ?? self::MAX_EXECUTION_TIME_LIMIT);
    }
}
