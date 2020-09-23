<?php

use Phalcon\DI\FactoryDefault as DI;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Db\Adapter\Pdo\Mysql as Mysql;
use App\Library\Url;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Config as Config;
use Phalcon\Http\Response\Cookies;
use App\Library\Profiler as ProfilerDb;
use Phalcon\Events\Manager as EventsManager;
use App\Library\Cryptography;
use Phalcon\Escaper;
use App\Library\Logger;
use App\Library\Security;
use App\Library\Tag as LibTag;

// Create a Dependency Injector
if (!isset($di))
    $di = new DI();

//Setup config
$di->setShared('config', function() use ($config, &$localConfig) {

    /**
     * loads and merges all config settings
     * we allow an [ENVIRONMENT => [...]] hash to be set which will get merged and override all settings in the base
     * order of priority is default config, then default config environment hash, then local config, and then local config environment 
     * (the later overrides the earlier so local config environment has the highest priority)
     * 
     * consider this:
     * in Config.php:
     * [...
     *  'environment' => 'local'
     *  'mysetting' => 1, 'mysetting2' => 1, 'mysetting3' => 1, 'mysetting4' => 1,
     *  'local' => ['mysetting' => 2, 'mysetting2' => 2, 'mysetting3' => 2],
     *  'development' => ['mysetting' => 3, 'mysetting2' => 3, 'mysetting3' => 3],
     * ]
     * in Config.Local.php:
     * [...
     *  'environment' => 'development'
     *  'mysetting' => 4, 'mysetting2' => 4,
     *  'local' => ['mysetting' => 5],
     *  'development' => ['mysetting' => 6],
     * ]
     * 
     * The values of the settings above would be
     * ['mysetting' => 6, 'mysetting2' => 4, 'mysetting3' => 3, 'mysetting4' => 1]
     * 
     * note that mysetting3 == 3 even though environment in the default config is 'local', it will take the local environment and then apply defaults from the default based on that local setting
     */
    $config = new Config($config);
    $environment = $config->get('environment'); // get the environment (need to get this just in case we don't set it in local config or the local config doesn't exist)

    if (isset($localConfig) && is_array($localConfig))
    {

        $localConfigObj = new Config($localConfig);

        $environment = $localConfigObj->get('environment', $environment);   // get the local environment falling back to the one already set if it doesn't exist (we need to do this first because of the order that we merge)

        if ($tmp = $config->get($environment))
            $config->merge($tmp);    // get the default environment config overrides
        $config->merge($localConfigObj);    // then merge in all local overrides
        if ($tmp = $localConfigObj->get($environment))
            $config->merge($tmp);    // and lastly merge in the environment local overrides
    } else
    {    // else since the local config is missing we will just take the the default environment from the default config
        if ($tmp = $config->get($environment))
            $config->merge($tmp);    // get the default environment config overrides
    }
    return $config;
});

$di->set('security', function() {
    $security = new Security();
    $security->setWorkFactor(10);
    return $security;
});

// default the timezone (should be later changed to the logged in user's/seller's/admin's config setting from the db) this is just the fallback
// really we could remove this is we set the php.ini config setting properly
if (isset($di->get('config')->timezone) && ($timezone = $di->get('config')->timezone))
    date_default_timezone_set($timezone);

$enableProfiler = $di->get('config')->db->get('debug');
if ($enableProfiler === null)
    $enableProfiler = isset($di->get('config')->environment) && in_array($di->get('config')->environment, ['local', 'development']);

// If the configuration specify the use of metadata adapter use it or use memory otherwise
$di->set('modelsMetadata', function() {
    return new MetaData();
});

// Register the flash service with custom CSS classes
$di->set('flash', function() {
    return new FlashSession(array(
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ));
});

// Setup Route
$di->set('router', function () use ($router) {
    return $router;
});

//Setup cookies
$di->set("cookies", function () {
    $cookies = new Cookies();
    $cookies->useEncryption(true);
    return $cookies;
});

//setup crypt
$di->set("crypt", function () {
    $crypt = new Cryptography();
    $crypt->setKey($this->get('config')->encryption->crypt_key); // Use your own key!
    return $crypt;
});

// Setup a Escaper
$di->set('escaper', function() {
    return new Escaper();
});

//File logger
$di->setShared('logger', function () {
    $logger = new Logger();
    $formatter = $logger->getFormatter();
    if ($formatter && ($formatter instanceof Phalcon\Logger\Formatter\Line))
    {
        $formatter->setDateFormat('H:i:s');
        $logger->setFormatter($formatter);
    }
    return $logger;
});

$di->setShared('tag', function () {
    $tag = new LibTag();
    return $tag;
});

// Setup a base URI so that all generated URIs include the "tutorial" folder
$di->set('url', function() {
    $config = $this->get('config');
    $url = new Url();
    $url->setBasePath(ROOTPATH);
    $url->setBaseUri($config->get('baseUrl', 'http://localhost'));
    $url->setStaticBaseUri($config->get('baseUrlStatic', $url->getBaseUri() . '/public'));
    return $url;
});

// Start the session the first time some component request the session service
$di->setShared('session', function() use ($di) {
    $sessionCookieExpireTime = 24 * 60 * 60;

    ini_set('session.gc_maxlifetime', $sessionCookieExpireTime);
    ini_set('session.cookie_lifetime', 0);

    $session = new SessionAdapter();
    $session->setName('SESSID');    // change name so we don't collide with another subdomain (if exists)
    $session->start();
    return $session;
});

if ($enableProfiler)
{
    //setup profiler
    $di->set("profiler", function () {
        return new ProfilerDb();
    }, true);
}

// Setup the database service
$di->set('db', function() use ($di, $enableProfiler) {

    if ($enableProfiler)
    {
        $eventsManager = new EventsManager();

        $profiler = null;
        if ($enableProfiler)
        {
            // Get a shared instance of the DbProfiler
            $profiler = $di->getProfiler();
        }

        // Listen all the database events
        $eventsManager->attach("db", function ($event, $connection) use ($di, $profiler, $enableProfiler) {
            if ($event->getType() === "beforeQuery")
            {
                if ($enableProfiler)
                {
                    $profiler->startProfile($connection->getSQLStatement(), $connection->getSqlVariables(), $connection->getSQLBindTypes());
                }
            }

            if ($event->getType() === "afterQuery")
            {
                if ($enableProfiler)
                {
                    $profiler->stopProfile();
                }
            }
        });
    }

    $dbConfig = array(
        "host" => $this->get('config')->db->host,
        "username" => $this->get('config')->db->username,
        "password" => $this->get('config')->db->password,
        "dbname" => $this->get('config')->db->dbname,
        "charset" => (isset($this->get('config')->db->charset) ? $this->get('config')->db->charset : 'utf8mb4'),
        "options" => (isset($this->get('config')->db->options) ? $this->get('config')->db->options->toArray() : []),
    );
    $connection = new Mysql($dbConfig);

    if ($enableProfiler)
    {
        $connection->setEventsManager($eventsManager);
    }

    /**
     * always set timezone to UTC in db, all date/datetime's should always be UTC
     * 
     * should really be set in my.cnf:
     * [mysqld]
     * default_time_zone='+00:00'
     * 
     * to view timezone you can run "SELECT @@global.time_zone, @@session.time_zone"
     */
    $connection->execute("SET time_zone = '+00:00'");

    return $connection;
});


