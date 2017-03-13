<?php

namespace App\Library\MyFooty\CustomLog;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

class CustomLog
{
    /**
     * Directory for log file
     *
     * @var string
     */
    protected $dir;

    /**
     * Filename for logfile
     *
     * @var string
     */
    private $filename;

    /**
     * Monolog Logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Log to the php console
     *
     * @var bool
     */
    protected $logToConsole;

    /**
     * Create a new DailyNotificationSender instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->logToConsole = false;
    }

    /**
     * Set log name
     *
     * @param string log name
     * @return void
     */
    public function setLogName($logName)
    {
        $this->logName = $logName;
    }

    /**
     * Get log name
     *
     * @return string log name
     */
    public function getLogName()
    {
        return $this->logName;
    }

    /**
     * Log with the info command
     *
     * @param string $logText The text to send to info
     * @param string $context Additional arguments to send to info
     * @return void
     */
    public function info($logText, $context = [])
    {
        if ($this->logger) {
            $this->logger->info($logText, $context);
        }
    }

    /**
     * Log with the error command
     *
     * @param string $logText The text to send to error
     * @param string $context Additional arguments to send to error
     * @return void
     */
    public function error($logText, $context = [])
    {
        if ($this->logger) {
            $this->logger->error($logText, $context);
        }
    }

    /**
     * Configure CustomLog instance
     *
     * @param string $dir        Directory name
     * @param string $filename   File name
     * @param bool $logToConsole Set should logging to the php console
     * @return void
     */
    public function configureLogger($dir, $filename, $logToConsole = false)
    {
        if (empty($dir)) {
            $message = "Invalid directory sent to CustomLog";
            throw new \InvalidArgumentException($message, 1);
        }

        if (empty($filename)) {
            $message = "Invalid filename sent to CustomLog";
            throw new \InvalidArgumentException($message, 1);
        }

        $this->dir = $dir;
        $this->filename = $filename;
        $this->logToConsole = $logToConsole;

        $this->setup();
    }

    /**
     * Setup the CustomLog
     *
     * @return void
     */
    public function setup()
    {
        $dir = $this->dir;
        $filename = $this->filename;
        $loggerPath = $this->dir . DIRECTORY_SEPARATOR . $filename . ".log";

        $lineFormatter = new LineFormatter(null, null, true, true);

        $fileHandler = new RotatingFileHandler($loggerPath, 0, \Monolog\Logger::INFO);
        $fileHandler->setFormatter($lineFormatter);

        $logger = new Logger('Cron Logs');
        $logger->pushHandler($fileHandler);

        if ($this->logToConsole) {
            $consoleHandler = new StreamHandler('php://stderr', 'debug');
            $consoleHandler->setFormatter($lineFormatter);
            $logger->pushHandler($consoleHandler);
        }

        $this->logger = $logger;
    }
}
