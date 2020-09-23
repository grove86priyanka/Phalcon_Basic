<?php

use Phalcon\Mvc\Application;

try
{
    define('DS', DIRECTORY_SEPARATOR);
    define('ROOTPATH', realpath('..') . DS);
    define('APPPATH', ROOTPATH . 'app' . DS);
    define('CONFIGPATH', APPPATH . 'config' . DS);
    
    require CONFIGPATH . 'Config.php';
    (@include $localConfigFile = CONFIGPATH . 'Config.Local.php') or ( copy(CONFIGPATH . 'Config.Local.Example.php', $localConfigFile) && (require $localConfigFile));
    require CONFIGPATH . 'Loader.php';
    require CONFIGPATH . 'Router.php';
    require CONFIGPATH . 'Application.php';

    // Handle the request
    $application = new Application($di);
    $di->setShared('app', function () use ($application) {
        return $application;
    });
    $eventsManager = $application->getEventsManager() ?: $di->getEventsManager() ?: new EventsManager();
    $application->setEventsManager($eventsManager);

    // Register the installed modules
    $application->registerModules($moduleConfigurations);
    echo $application->handle()->getContent();
} catch (\Phalcon\Exception $e)
{
    echo "Phalcon\Exception: ", $e->getMessage();
    try
    {
        // log the exception since we likely missed it before
        $di->getShared('logger')->error("Exception: " . $e->getMessage());
    } catch (\Exception $e)
    {
        
    }
} catch (Exception $e)
{
    echo "Exception: ", $e->getMessage();
    try
    {
        // log the exception since we likely missed it before
        $di->getShared('logger')->error("Exception: " . $e->getMessage());
    } catch (\Exception $e)
    {
        
    }
}

