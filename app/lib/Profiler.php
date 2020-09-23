<?php

/**
 * Displays transactions made on the database and the times them taken to execute
 *
 * @author amit
 */

namespace App\Library;

use Phalcon\Db\Profiler as DbProfiler;
use Phalcon\Db\Profiler\Item;

class Profiler extends DbProfiler
{

    private $logger = null;

    public function __construct()
    {
        $this->logger = $this->getLogger();
        if (!$this->logger->isTransaction())
        {
            $this->logger->begin();
        }
    }

    /**
     * 
     * @param Phalcon\Db\Profiler\Item $profile
     */
    public function beforeStartProfile(Item $profile)
    {
        $this->logger->debug('SQL: ' . $profile->getSqlStatement());
        $this->logger->debug('Start Time: ' . $profile->getInitialTime());
    }

    /**
     * Implement afterEndProfile()
     * Logging profile [ SQL statement ] after profile end
     * @param Phalcon\Db\Profiler\Item $profile
     */
    public function afterEndProfile(Item $profile)
    {
        $this->logger->debug('End Time: ' . $profile->getFinalTime());
        $this->logger->debug('Total Elapsed seconds: ' . $profile->getTotalElapsedSeconds());
        $this->logger->debug('--------------------------------------------------');

        if ($this->logger->isTransaction())
        {
            $this->logger->commit();
        }
    }

    protected function getLogger()
    {
        if (!$this->logger)
        {
            $di = \Phalcon\Di::getDefault();
            $config = $di->getConfig();

            $currentDate = \App\Helper\Date::currentTimeDate('Y-m-d');

            $loggerFilename = $currentDate . ".log";
            $loggerFileDest = $config->logger->filePath . "db" . DS;

            $this->logger = new \Phalcon\Logger\Adapter\File($loggerFileDest . $loggerFilename);
            $formatter = $this->logger->getFormatter();
            if ($formatter && ($formatter instanceof \Phalcon\Logger\Formatter\Line))
            {
                $formatter->setDateFormat('H:i:s');
                $this->logger->setFormatter($formatter);
            }
        }

        return $this->logger;
    }

    public function __destruct()
    {
        register_shutdown_function(function () {
            $this->logger->close();
        });
    }

}
