<?php

$selfdir=dirname(__FILE__);
$approot=dirname(__DIR__);
$dirExternal="external";
$dirBuild="build";
$dirPackages="built_packages";

// ---------- build with spc

$php_app="Ht-manager";

// php version for spc
$php_version="8.4.4";

// extensions - see https://static-php.dev/en/guide/extensions.html
$aPhpLibs=[
    "readline" => true,
    "zlib" => false,
];

$php_libs="";
foreach($aPhpLibs as $key => $bEnabled){
    if($bEnabled){
        $php_libs.=$bEnabled  ? ($php_libs ? ",":"").$key : '';
    }
}

// TODO: how can it be dynamic?
$myarchitecture="x86_64";
// $myarchitecture="aarch64";

$myos=strtolower(PHP_OS);

// TODO: check value on MS Windows
switch ($myos) {
    case 'cygwin_nt-5.1':
    case 'windows':
    case 'winnt':
        $myos="windows";
        $myarchitecture="x64";
        break;
    case 'win32':
        $myos="win";
        $myarchitecture="i386";
        break;
    case 'darwin':
        $myos="macos";
        break;
}
$myosextension=$myos=="windows" ? ".exe" : "";

$SPC=str_replace('/', DIRECTORY_SEPARATOR , "$approot/$dirExternal/bin/spc$myosextension");

$cmdSpcDownload="$SPC download --no-interaction --with-php=$php_version --for-extensions \"$php_libs\"";
$cmdSpcBuild="$SPC build --no-interaction --build-micro \"$php_libs\"";
