#!/bin/php 
<?php

chdir(__DIR__);
$FOLDER=dirname(__DIR__);
global $_VERSION; $_VERSION="0.2";

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

require_once '../vendor/ahcli/cli.class.php';
require_once('../vendor/class-htaccess/src/htgroup.class.php');
require_once('../vendor/class-htaccess/src/htpasswd.class.php');


$aParamDefs=[
    'label' => 'HT Manager',
    'description' => 'Manage htpasswd and htgroups',
    'params'=>[
        'folder'=>[
            'short' => 'f',
            'value'=> CLIVALUE_REQUIRED,
            'pattern'=>'/./i',
            'shortinfo' => 'Folder name where to put .htpasswd an .htgroup',
            'description' => 'Set a folder where to handle the .htpasswd and .htgroup files.',
        ],

        'help'=>[
            'short' => 'h',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'Show help and exit',
            'description' => '',
        ],
        'version'=>[
            'short' => 'v',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'Show version and exit',
            'description' => '',
        ],
    ],
];

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

function h2($sText){
    global $oCli;
    echo $oCli->getColor('purple');
    echo PHP_EOL."#####|  $sText".PHP_EOL.PHP_EOL;
}

function showheader(){
    global $oCli, $_VERSION;
    echo $oCli->getcolor('purple');      echo "      _____________".PHP_EOL;
    echo $oCli->getcolor('purple');      echo "_____/  A x e l s  \_________________________________________________".PHP_EOL;
    // echo PHP_EOL;
    echo $oCli->getcolor('blue');        echo "   _______ __                                                      ".PHP_EOL;
    echo $oCli->getcolor('blue');        echo "  |   |   |  |_ ______.--------.---.-.-----.---.-.-----.-----.----.".PHP_EOL;
    echo $oCli->getcolor('cyan');        echo "  |       |   _|______|        |  _  |     |  _  |  _  |  -__|   _|".PHP_EOL;
    echo $oCli->getcolor('green');       echo "  |___|___|____|      |__|__|__|___._|__|__|___._|___  |_____|__|  ".PHP_EOL;
    echo $oCli->getcolor('light green'); echo "                                                 |_____|      v$_VERSION".PHP_EOL;
    //echo PHP_EOL;
    $oCli->color('reset');
}

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

// init the class with the config array
$oCli=new axelhahn\cli($aParamDefs);

if ($oCli->getvalue("help")){
    showheader();
    echo "  👤 Author: Axel Hahn".PHP_EOL;
    echo "  🧾 Source: https://github.com/axelhahn/ht-manager/".PHP_EOL;
    echo "  📜 License: GNU GPL 3.0".PHP_EOL;
    echo PHP_EOL;
    echo $oCli->showhelp();
    exit(0);
}
if ($oCli->getvalue("version")){
    echo $_VERSION.PHP_EOL;
    exit(0);
}


$FOLDER=$oCli->getvalue("folder")?:$FOLDER;
if(!is_dir($FOLDER)){
    $oCli->color('error');
    echo "ERROR: Folder '$FOLDER' doesn't exist.".PHP_EOL.PHP_EOL;
    $oCli->color('reset');
    exit(1);
}

while(true){
    // clear screen
    $oHtgroup=new axelhahn\htgroup("$FOLDER/.htgroup");
    $oHtpasswd=new axelhahn\htpasswd("$FOLDER/.htpasswd");

    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
    showheader();

    $oCli->color('info', "  Folder: '$FOLDER'".PHP_EOL.PHP_EOL);
    echo $oCli->getcolor('yellow');
    // echo "_____________________________________________________________________".PHP_EOL;
    // echo PHP_EOL;

    $sFormat="  %-30s %-30s".PHP_EOL;
    $sDelim=":  ";
    printf($sFormat, "USERS: " . count($oHtpasswd->list()), "{$sDelim}GROUPS: ".count($oHtgroup->list()) );
    $oCli->color('reset');
    printf($sFormat, "", $sDelim);
    printf($sFormat, "s - show .htpasswd", "{$sDelim}S - show .htgroup", "");
    printf($sFormat, "", $sDelim);
    printf($sFormat, "a - Add user", "{$sDelim}A - Add group");
    printf($sFormat, "l - List users", "{$sDelim}L - List groups");
    printf($sFormat, "r - Remove user", "{$sDelim}R - Remove group");
    printf($sFormat, "", "{$sDelim}U - Add user to group");
    printf($sFormat, "", "{$sDelim}D - Delete user from group");

    echo PHP_EOL;
    echo "  f - set another folder".PHP_EOL;
    echo "  q - quit".PHP_EOL;
    echo PHP_EOL;

    $sMenu=$oCli->_cliInput(" > ", "");

    switch ($sMenu) {
        case 'q':
            $oCli->color('info', "Bye.".PHP_EOL);
            echo PHP_EOL;
            exit(0);

        case 's':
            $sFile=$oHtpasswd->getFile();
            h2("File: $sFile");
            if(file_exists($sFile)){
                $oCli->color("cli");
                echo file_get_contents($sFile);
                $oCli->color("reset");
            } else {
                $oCli->color('error', "ERROR: File '$sFile' doesn't exist yet.".PHP_EOL);
            }
            break;

        case 'S':
            $sFile=$oHtgroup->getFile();
            h2("File: $sFile");
            if(file_exists($sFile)){
                $oCli->color("cli");
                echo file_get_contents($sFile);
                $oCli->color("reset");
            } else {
                $oCli->color('error', "ERROR: File '$sFile' doesn't exist yet.".PHP_EOL);
            }
            break;

        case 'a':
            h2("Add user");
            $sUser=$oCli->_cliInput("Username : ", "");
            if ($sUser) {
                $sPW=$oCli->_cliInput("Password : ", "");
                if ($sPW) {
                    $oHtpasswd->debug(true);
                    $bSuccess=$oHtpasswd->add($sUser, $sPW);
                    $oHtpasswd->debug(false);
                    if(!$bSuccess){
                        $oCli->color('error', "Action failed.".PHP_EOL);
                    }
                } else {
                    $oCli->color('error', "Doing nothing - password is empty.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'A':
            h2("Add group");
            $sGroup=$oCli->_cliInput("Groupname : ", "");
            if ($sGroup) {
                $oHtgroup->debug(true);
                $bSuccess=$oHtgroup->add($sGroup);
                $oHtgroup->debug(false);
                if(!$bSuccess){
                    $oCli->color('error', "Action failed.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'l':
            h2("List users");
            $oCli->color('cli');
            foreach(array_keys($oHtpasswd->list()) as $sUser){
                $sGroups="";
                foreach ($oHtgroup->list() as $sGroup){
                    if (in_array($sUser, $oHtgroup->members($sGroup))) {
                        $sGroups.=($sGroups ? ", ": "") . "$sGroup";
                    }
                }
                printf("%-10s %s\n", "$sUser", $sGroups ? "($sGroups)": "");
            }
            if(count($oHtpasswd->list())==0){
                $oCli->color('error', "No users found.".PHP_EOL);
            }
            $oCli->color('reset');
            break;

        case 'L':
            h2("List groups");
            $oCli->color('cli');
            foreach ($oHtgroup->list() as $sGroup){
                echo "$sGroup".PHP_EOL;
            }
            if(count($oHtgroup->list())==0){
                $oCli->color('error', "No group was found.".PHP_EOL);
            }
            $oCli->color('reset');
            break;

        case 'r':
            h2("Remove a user");
            $oCli->color('cli');
            foreach(array_keys($oHtpasswd->list()) as $sUser){
                echo "$sUser".PHP_EOL;
            }
            $sUser=$oCli->_cliInput("Username : ", "");
            if($sUser){
                $oHtpasswd->debug(true);
                $bSuccess=$oHtpasswd->remove($sUser);
                $oHtpasswd->debug(false);
                if(!$bSuccess){
                    $oCli->color('error', "Action failed.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;
        case 'R':
            h2("Remove a group");
            $oCli->color('cli');
            foreach($oHtgroup->list() as $sGroup){
                echo "$sGroup".PHP_EOL;
            }
            $sGroup=$oCli->_cliInput("Groupname : ", "");
            if($sGroup){
                $oHtgroup->debug(true);
                $bSuccess=$oHtgroup->remove($sGroup);
                $oHtgroup->debug(false);
                if(!$bSuccess){
                    $oCli->color('error', "Action failed.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'U':
            h2("Add user to group");
            $oCli->color('cli');
            foreach(array_keys($oHtpasswd->list()) as $sUser){
                echo "$sUser".PHP_EOL;
            }
            $sUser=$oCli->_cliInput("Username : ", "");
            if($sUser){
                if($oHtpasswd->exists($sUser)){
                    
                    $oCli->color('cli');
                    foreach($oHtgroup->list() as $sGroup){
                        echo "$sGroup".PHP_EOL;
                    }
                    $sGroup=$oCli->_cliInput("Groupname : ", "");
                    if($sGroup){
                        $oHtgroup->debug(true);
                        $bSuccess=$oHtgroup->userAdd($sUser, $sGroup);
                        $oHtgroup->debug(false);
                        if(!$bSuccess){
                            $oCli->color('error', "Action failed.".PHP_EOL);
                        }
                    } else {
                        $oCli->color('error', "Doing nothing - no group was given.".PHP_EOL);
                    }
                } else {
                    $oCli->color('error', "Doing nothing - User '$sUser' does not exist.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing - no user was given.".PHP_EOL);
            }
            break;

        case 'D':
            h2("Delete a user from a group");
            $oCli->color('cli');
            foreach($oHtgroup->list() as $sGroup){
                echo "$sGroup".PHP_EOL;
            }
            $sGroup=$oCli->_cliInput("Groupname : ", "");
            if($sGroup){
                if($oHtgroup->exists($sGroup)){
                    $oCli->color('cli');
                    $bUserfound=false;
                    foreach($oHtgroup->members($sGroup) as $sUser){
                        echo "$sUser".PHP_EOL;
                        $bUserfound=true;
                    }
                    if(!$bUserfound){
                        $oCli->color('error', "Group '$sGroup' has no members.".PHP_EOL);
                    } else {
                        $sUser=$oCli->_cliInput("Username : ", "");
                        if($sUser){
                            $oHtgroup->debug(true);
                            $bSuccess=$oHtgroup->userRemove($sUser, $sGroup);
                            $oHtgroup->debug(false);
                            if(!$bSuccess){
                                $oCli->color('error', "Action failed.".PHP_EOL);
                            }
                        } else {
                            $oCli->color('error', "Doing nothing - no user was given.".PHP_EOL);
                        }
                    }
                } else {
                    $oCli->color('error', "Group '$sGroup' doesn't exist.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing - no group was given.".PHP_EOL);
            }
            break;
        case 'f':
            h2("Set a new folder");
            $newfolder=$oCli->_cliInput("Folder : ", "");
            if($newfolder){
                if(is_dir($newfolder)){
                    $FOLDER=realpath($newfolder);
                } else {
                    $oCli->color('error', "Folder '$newfolder' doesn't exist.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing - keeping current folder ...".PHP_EOL);
            }
            break;

        default:
            $oCli->color('error', "Unknown command. Please try again.".PHP_EOL);
            break;
    }
    echo PHP_EOL;
    $dummy=$oCli->_cliInput("Press <RETURN> to go back to menu ", "");
}
