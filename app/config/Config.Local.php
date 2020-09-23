<?php

/**
 * this file is an example configuration of what you need to set in your environment
 * the first time any page is loaded a copy will be made to Config.Local.php so you can easily edit the settings
 * after that is up to you to maintain the correct settings when changes are needed
 * use this file to push new "suggestions" to the repo so 
 *  a) new created repos will get latest config settings needed
 *  b) existing repos will see the file changed and know there is a new configuration setting that might need to be set in their Config.Local.php copy
 * you can provide reasonable defaults in this file to settings that might likely need to be changed or settings that you want to persist (never automatically change from a later commit)
 * settings that do not need to be overridden can just be set in Config.php
 * any setting that is in Config.php can be addeded to Config.Local.php to override it, only add/commit it to Config.Example.php if you would like to persist it as a suggested setting in the future
 * 
 * it is safe and recommended to commit this file (Config.Local.Example.php) 
 * you should NEVER commit Config.Local.php to the repo, make sure you add app/config/Config.Local.php to .gitignore
 * 
 * 
 * 
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
$localConfig = [
    "environment" => 'local:8080', // local|development|staging|production
    "baseUrl" => 'http://localhost:8080/demo/', // need to set it here since on cli we don't know what the requested domain is since there is no "webserver"
//            "baseUrlBackend" => 'http://localhost', // used for backend pages that we don't want to go through cloudflare's full page cache (needed for long running requests like csv/bulk image)
//            "baseUrlStatic" => 'http://localhost/public',
    "db" => [
//                "host" => "localhost",
//                "username" => "root",
        "password" => "",
        "dbname" =>"demo",
//                "debug" => false // 'echo'|true|false
    ],
	"email" => [
                "smtpHost" => "smtp.gmail.com",
                "smtpPort" => "587",
                "smtpSecurity" => "tls",
                "smtpUsername" => "v2btechmail@gmail.com",
                "smtpPassword" => "v2btech@test",
                "isSmtp" => TRUE,
                
                "testing" => true,    // enables testing for all emails
                "testing_email" => 'priyanka@jbktechnologies.com',
            ],
	
//            'webserver_user' => '_www',   // used for changing log file owner/group to fix permissions when running both cli (root) and apache
//            'webserver_group' => '_www',   // used for changing log file owner/group to fix permissions when running both cli (root) and apache
];
