<?php

$config = [
    "db" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "<YOUR_DB_PASSWORD>",
        "dbname" => "<YOUR_DB_NAME>",
        "charset" => "utf8mb4",
        "options" => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            PDO::MYSQL_ATTR_LOCAL_INFILE => true // To enable "LOAD DATA LOCAL INFILE"
        ),
        "logDir" => (APPPATH . 'log' . DS),
        "debug" => false, // 'echo'|true|false|null
    ],
    "view" => [
        "layoutsDir" => APPPATH . 'template' . DS . 'layouts' . DS,
        "partialsDir" => APPPATH . 'template' . DS . 'layouts' . DS . 'common' . DS,
        "mainView" => APPPATH . 'template' . DS . 'index',
    ],
    "encryption" => [
        "crypt_key" => '@D1f6#Gdpd%f^Yj8V$1',
    ],
    "common" => [
        "admin_email" => 'admin@grove86.com',
        "inquire_email" => 'inquiries@grove86.com',
        "admin_email_name" => 'Grove86',
    ],
    "email" => [
        "smtpHost" => "",
        "smtpPort" => "",
        "smtpSecurity" => "",
        "smtpUsername" => "",
        "smtpPassword" => "",
        "isSmtp" => TRUE,
        "testing" => true, // enables testing for all emails
        "testing_email" => 'test@grove86.com',
    ],
    'timezone' => 'America/New_York',
    'environment' => 'production', // local|development|staging|production
    'nacl' => '97!FxNEM', // WARNING! DO NOT CHANGE!!
    
    'logger' => [
        'filePath' => APPPATH . 'log' . DS
    ],
    /* set all environment default settings */
    'local' => [
        'image' => [
            'engine' => 'local',
            'dirPrefix' => ROOTPATH . 'public' . DS . 'img' . DS,
            'urlPrefix' => 'img/',
        ],
    ],
    'development' => [
        "email" => [
            "testing_email" => 'testdev@grove86.com',
        ],
    ],
    'staging' => [
        "email" => [
            "testing_email" => 'teststag@grove86.com',
        ],
    ],
    'production' => [
        'image' => [
            'baseUrl' => 'https://images.grove86.com',
        ],
        "email" => [
            "testing" => false, // turn testing off for prod environment
            "testing_email" => 'testprod@grove86.com', // but still change the email so its to a different
        ],
    ],
    'webserver_user' => 'apache', // used for changing log file owner/group to fix permissions when running both cli (root) and apache
//    'webserver_group' => 'apache',   // used for changing log file owner/group to fix permissions when running both cli (root) and apache
    'assetVersions' => [
        'js' => [
        /* 'functions.js' => '0.1.0',
          'jquery.js' => '0.0.1',
          'jquery-ui.js' => '0.0.1',
          'uploadfile.js' => '0.0.5',
          'chosen.js' => '0.0.1',
          'bootstrap.min.js' => '0.0.1', */
        ],
        'css' => [
        /* 'custom.css' => '0.0.3',
          'admin.css' => '0.2.0',
          'jquery-ui.css' => '0.0.1',
          'uploadfile.css' => '0.0.2',
          'bootstrap.min.css' => '0.0.1',
          'responsive.css' => '0.0.3',
          'font-awesome.css' => '0.0.2', */
        ]
    ],
];

define('FRONT_MODULES_PATH', APPPATH . 'modules' . DS . 'front' . DS);
define('USER_MODULES_PATH', APPPATH . 'modules' . DS . 'user' . DS);
define('ADMIN_MODULES_PATH', APPPATH . 'modules' . DS . 'admin' . DS);

define('IMG_UPLOAD_DIR', DS . 'img' . DS . 'u' . DS);
define('IMG_UPLOAD_PATH', ROOTPATH . 'public' . IMG_UPLOAD_DIR);
