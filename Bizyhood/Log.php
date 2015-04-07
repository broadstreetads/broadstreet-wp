<?php
/**
 * This file contains a class for logging messages to files on disk
 */

/**
 * A class for logging statements to log file. Essentially a modified form of
 *  KLogger
 * 
 * @link http://github.com/katzgrau/KLogger
 */
class Bizyhood_Log
{
    const DEBUG     = 1;    // Most Verbose
    const INFO      = 2;    // ...
    const WARN      = 3;    // ...
    const ERROR     = 4;    // ...
    const FATAL     = 5;    // Least Verbose
    const OFF       = 6;    // Nothing at all.

    const LOG_OPEN    = 1;
    const OPEN_FAILED = 2;
    const LOG_CLOSED  = 3;

    /**
     * Holds the current status of the log (open, closed, etc)
     * @var int
     */
    private $_logStatus                = self::LOG_CLOSED;

    /**
     * Holds the format to use for the timestamp in the log statements
     * @todo Move to config
     * @var int
     */
    private static $_dateFormat        = "Y-m-d G:i:s";

    /**
     * Default permissions to use when setting up log directories if they don't
     *  exist
     * @var int
     */
    private static $_defaultPermissions= 0777;

    /**
     * An array of messages which stores warnings and information int eh event
     *  a log file can't be opened for reading
     * @var array
     */
    public $_messageQueue              = array();

    /**
     * Will hold the location of the log file
     * @var string
     */
    private $_logFile                  = NULL;

    /**
     * Will hold the logging level/priority
     * @var int
     */
    private $_priority                 = self::INFO;

    /**
     * Will hold the PHP resource handle of the open log file
     * @var resource
     */
    private $_fileHandle               = NULL;

    /**
     * Will hold the instantiated instance of the logging object
     * @var array
     */
    private static $instances = array();

    /**
     * Get a open and ready instance of the logging class.
     * @param string $logDirectory Optionally specify a directory to log to
     * @return Bizyhood_Log
     */
    public static function instance($logDirectory = FALSE, $priority = FALSE)
    {
        # Didn't set a priority? Well, I suppose you'll see everything
        if($priority) $priority = Bizyhood_Config::get('log.level');

        # Directory doesn't exist? Created that directory (and all
        # directories on the way)
        if($logDirectory === FALSE)
        {
            $logDirectory = Bizyhood_Config::get('log.directory');
        }

        # Do we already have an instance of a logger for this directory?
        # Grab it! If not, whip up a new one.
        if(in_array($logDirectory, self::$instances))
        {
            return self::$instances[$logDirectory];
        }

        self::$instances[$logDirectory] = new self($logDirectory, $priority);

        # Return the logger
        return self::$instances[$logDirectory];
    }

    /**
     * Lod a
     * @param string $level A string of either:
     *  'debug', 'info', 'warn', 'error', or 'fatal'
     * @param string $message A message to log to the file
     */
    public static function add($level, $message)
    {
        self::instance()->log($message, self::_stringToLevel($level));
    }

    /**
     * Convert a string of 'debug', 'info', 'warn', 'error', or 'fatal' to it's
     *  corresponding internal Bizyhood_Log level
     * @param string $string The level to convert to a usuable log level
     * @return int the Bizyhood_Log logging level
     */
    private static function _stringToLevel($string)
    {
        $string = strtolower($string);

        if($string == 'debug')     return self::DEBUG;
        elseif($string == 'info')  return self::INFO;
        elseif($string == 'warn')  return self::WARN;
        elseif($string == 'error') return self::ERROR;
        elseif($string == 'fatal') return self::FATAL;
        else return self::OFF;
    }

    /**
     *
     * @param string $logDirectory
     * @param int $priority
     * @return False is and only if the log can't be opened (permissions)
     */
    public function __construct($logDirectory, $priority)
    {
        $logDirectory = rtrim($logDirectory, '/');

        if($priority == self::OFF) return;

        $this->_logFile  = $logDirectory
                           . DIRECTORY_SEPARATOR
                           . 'log_'
                           . date('Y-m-d')
                           . '.txt';

        $this->_priority = $priority;
        if(!file_exists($logDirectory))
        {
            @mkdir($logDirectory, self::$_defaultPermissions, TRUE);
        }

        if(file_exists($this->_logFile))
        {
            if (!is_writable($this->_logFile))
            {
                $this->_logStatus      = self::OPEN_FAILED;
                $this->_messageQueue[] = "The file exists, but could not be opened for writing. Check that appropriate permissions have been set.";
                return;
            }
        }

        if(($this->_fileHandle = @fopen($this->_logFile, "a" )))
        {
            $this->_logStatus      = self::LOG_OPEN;
            $this->_messageQueue[] = "The log file was opened successfully.";
        }
        else
        {
            $this->_logStatus      = self::OPEN_FAILED;
            $this->_messageQueue[] = "The file could not be opened. Check permissions.";
        }
    }

    public function __destruct()
    {
        if ($this->_fileHandle)
            fclose($this->_fileHandle);
    }

    public function logInfo($line)
    {
        $this->log($line, self::INFO);
    }

    public function logDebug($line)
    {
        $this->log($line, self::DEBUG);
    }

    public function logWarn($line)
    {
        $this->log($line, self::WARN);
    }

    public function logError($line)
    {
        $this->log($line, self::ERROR);
    }

    public function logFatal($line)
    {
        $this->log($line, KLogger::FATAL);
    }

    public function log($line, $priority)
    {
        if($this->_priority <= $priority)
        {
            $status = $this->_getTimeLine($priority);
            $this->writeFreeFormLine ("$status $line \n");
        }
    }

    public function writeFreeFormLine($line)
    {
        if ( $this->_logStatus == self::LOG_OPEN
             && $this->_priority != self::OFF)
        {
            if (fwrite($this->_fileHandle, $line) === FALSE)
            {
                $this->_messageQueue[] = "The file could not be written to. Check that appropriate permissions have been set.";
            }
        }
    }

    private function _getTimeLine($level)
    {
        $time = date(self::$_dateFormat);

        switch($level)
        {
            case self::INFO:
                return "$time - INFO  -->";
            case self::WARN:
                return "$time - WARN  -->";
            case self::DEBUG:
                return "$time - DEBUG -->";
            case self::ERROR:
                return "$time - ERROR -->";
            case self::FATAL:
                return "$time - FATAL -->";
            default:
                return "$time - LOG   -->";
        }
    }

    public function getLogFilePath()
    {
        return $this->_logFile;
    }
}