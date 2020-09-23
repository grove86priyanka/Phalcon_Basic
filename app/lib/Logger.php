<?php

/**
 * Logger Class
 *
 * @author amit
 */

namespace App\Library;

use Phalcon\Logger\Adapter\File as FileAdapter;

class Logger extends FileAdapter
{

    protected $historyMaxSize = 1000;
    protected $history = [];

    /**
     * Logger Constructor
     */
    public function __construct()
    {

        $di = \Phalcon\Di::getDefault();
        $config = $di->getConfig();

        $webUser = $config->get('webserver_user', 'apache');
        $webGrp = $config->get('webserver_group', $webUser);

        $loggerFile = $this->loggerFilename();
        $umask = umask(0111);   // default to rw for all

        parent::__construct($loggerFile);

        try
        {
            @chown($loggerFile, $webUser);  // try to set it back to the webserver if exists and we have permission, else just rely on the umask from above
            @chgrp($loggerFile, $webGrp);  // try to set it back to the webserver if exists and we have permission, else just rely on the umask from above
        } catch (\ErrorException $e)
        {
            
        } catch (\Exception $e)
        {
            
        }
        umask($umask);
    }

    /**
     * Generate the log file name format: yyyy-mm-dd.log [ex. 2017-12-12.log]
     * Therefore the log file will change everyday and will be automatically archived
     * @return string
     */
    private function loggerFilename()
    {
        $di = \Phalcon\Di::getDefault();
        $config = $di->getConfig();

        $currentDate = \App\Helper\Date::currentTimeDate('Y-m-d');

        $loggerFilename = $currentDate . ".log";
        $loggerFileDest = $config->logger->filePath;

        return $loggerFileDest . $loggerFilename;
    }

    public function __destruct()
    {
        $this->close(); // only close on destruct since we might log on register_shutdown_function
    }

    public function logInternal($message, $type, $time, array $context)
    {
        $this->appendHistory($message, $type, $time, $context);
        return parent::logInternal($message, $type, $time, $context);
    }

    public function enableHistory($setting = true)
    {
        return $this->historyMaxSize = ($setting ? (is_numeric($setting) ? $setting : 1000) : $setting);
    }

    public function clearHistory()
    {
        return $this->history = [];
    }

    public function getHistory()
    {
        if ($this->historyMaxSize)
        {

            // need to add in history from the "transaction" on the fly, we can't store it in history because we don't have any way of tracking which items where already added
            // similarly the "clearHistory" will never clear the history from the _queue, if you need to clear that you need to "flush" the logger by calling commit()
            $return = array_merge($this->history ?: [], array_map(function ($item) {
                        if ($item instanceof \Phalcon\Logger\Item)
                            $item = $this->getMessage($item->getMessage(), $item->getType(), $item->getTime(), $item->getContext());
                        return $item;
                    }, $this->_queue ?: []));

            if ($this->historyMaxSize > 0 && sizeof($return) > $this->historyMaxSize)
                $return = array_slice($return, $this->historyMaxSize * -1);

            return $return;
        }
    }

    private function appendHistory($message, $type, $time, $context)
    {
        if ($this->historyMaxSize)
        {
            $this->history[] = $this->getMessage($message, $type, $time, $context);
            if ($this->historyMaxSize > 0 && sizeof($this->history) > $this->historyMaxSize)
                $this->history = array_slice($this->history, $this->historyMaxSize * -1);
        }
    }

    /**
     * Phalcon has no way to get the formatted message so we can easily recreate it here
     * 
     * @param type $message
     * @param type $type
     * @param type $time
     * @param type $context
     * @return type
     */
    public function getMessage($message, $type, $time, $context = null)
    {
        $formatter = $this->getFormatter();
        if ($formatter && ($formatter instanceof \Phalcon\Logger\FormatterInterface))
        {
            return $formatter->format($message, $type, $time, $context);
        }
    }

    /**
     * overload so we can insert referrer and request url
     * 
     * @param type $message
     * @param array $context
     * @param type $appendPage include the page
     * @param type $appendReferrer
     * @return type
     */
    public function error($message, array $context = null, $appendPage = true, $appendReferrer = false)
    {

        $page = $referrer = '';
        if ($appendPage && isset($_SERVER['REQUEST_URI']))
            $page = $_SERVER['REQUEST_URI'];
        if ($appendReferrer && isset($_SERVER['HTTP_REFERER']))
            $referrer = $_SERVER['HTTP_REFERER'];
        if ($page || $referrer)
        {
            if (!$context)
                $context = [];
            if ($page)
            {
                $context['request_uri'] = $page;
                if (stripos($message, '{request_uri}') === false)
                    $message .= ', {request_uri}';
            }
            if ($referrer)
            {
                $context['http_referer'] = $referrer;
                if (stripos($message, '{http_referer}') === false)
                    $message .= ', ref: {http_referer}';
            }
        }

        return parent::error($message, $context);
    }

}
