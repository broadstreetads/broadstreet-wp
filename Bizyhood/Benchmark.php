<?php
/**
 * This file contains a class for measuring performance of the search requests
 *  and internal functionality.
 */

/**
 * A class for helping to measure and log performance. Essentially, acts as
 *  a easy to use timer
 */
class Bizyhood_Benchmark
{
    private static $_timers = array();

    /**
     * Start a timer for a given description. Writes the starting point to the
     *  log automatically.
     * @param string $timer_description A unique string that describes this timer
     */
    public static function start($timer_description = 'Anonymous Timer')
    {
        self::$_timers[$timer_description] = microtime(true);
        Bizyhood_Log::add('info', "Starting benchmark: $timer_description");
    }

    /**
     * The name of a timer that has already been started. Writes the stopping
     *  point and result to the log automatically
     * @param string $timer_description The description of a timer that has
     *  already been started
     * @return The number of seconds elapsed if the timer exists, false if it
     *  doesn't
     */
    public static function stop($timer_description)
    {
        if(array_key_exists($timer_description, self::$_timers))
        {
            $start   = self::$_timers[$timer_description];
            $stop    = microtime(true);
            $seconds = round($stop - $start, 6);
            
            Bizyhood_Log::add('info', "Stopped benchmark: $seconds seconds for '$timer_description'");
            return $seconds;
        }
        else
        {
            Bizyhood_Log::add('warn', "Unknown benchmark 'stopped': $timer_description");
            return FALSE;
        }
    }
}
