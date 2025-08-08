<?php
/**
 * 
 * APP SPECIFIC CONFIG FOR BUILD PROCESS
 * 
 */

return [
    'appname'=>"Ht-manager",
    'php'=>[
        "version"=>"8.4.4",
        "libs" => [
            "readline" => true,
            "zlib" => false,
        ],
    ],
    "main" => "src/htman.php",
    "merge" => [
        "vendor/ahcli/cli.class.php",
        "vendor/class-htaccess/src/htgroup.class.php",
        "vendor/class-htaccess/src/htpasswd.class.php",
    ],
    "replace" => [
        "strings" => [
            "namespace axelhahn;" => "",
            "new axelhahn\cli(" => "new cli(",
            "new axelhahn\htgroup(" => "new htgroup(",
            "new axelhahn\htpasswd(" => "new htpasswd(",
        ],
    ],

];

