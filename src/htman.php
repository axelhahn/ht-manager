#!/bin/php 
<?php

chdir(__DIR__);
$FOLDER=dirname(__DIR__);
global $_VERSION; $_VERSION="0.4";

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
        'debug'=>[
            'short' => 'd',
            'value'=> CLIVALUE_NONE,
            'shortinfo' => 'Debug mode.',
            'description' => 'Show debug messages on actions.',
        ],

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

$bDebugActions=false;

// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

/**
 * Show a header and an optional hint text for the current function
 * 
 * @param string $sText  text of headline
 * @param string $sHint  optional: hint text
 * @return void
 */
function h2($sText, $sHint=""){
    global $oCli;
    echo $oCli->getColor('purple');
    echo "      ____".str_repeat("_", strlen($sText));
    echo PHP_EOL."_____/  $sText  \\".str_repeat("_", 58-strlen($sText))
        .PHP_EOL.PHP_EOL;
    $oCli->color('reset');
    if($sHint) {
        echo $oCli->getColor('blue') ."  $sHint".PHP_EOL.PHP_EOL;
    }
    $oCli->color('reset');
}

/**
 * Show a header
 * @return void
 */
function showheader(){
    global $oCli, $_VERSION;
    h2("A x e l s");
    echo $oCli->getcolor('blue');        echo "   _______ __                                                      ".PHP_EOL;
    echo $oCli->getcolor('blue');        echo "  |   |   |  |_ ______.--------.---.-.-----.---.-.-----.-----.----.".PHP_EOL;
    echo $oCli->getcolor('cyan');        echo "  |       |   _|______|        |  _  |     |  _  |  _  |  -__|   _|".PHP_EOL;
    echo $oCli->getcolor('green');       echo "  |___|___|____|      |__|__|__|___._|__|__|___._|___  |_____|__|  ".PHP_EOL;
    echo $oCli->getcolor('light green'); echo "                                                 |_____|      v$_VERSION".PHP_EOL;
    //echo PHP_EOL;
    $oCli->color('reset');
}

/**
 * Menu helper. show item in enabled or disabled state
 * @param bool $bVisibility  flag: is menu item enabled?
 * @param string $sChar      char for menu item
 * @param string $sText      label of menu item
 * @return string
 */
function _m(bool $bVisibility, string $sChar,string $sText){
    global $oCli;
    $sKey=$sChar ? "$sChar " : "";
    return ($bVisibility
        ? ($oCli->getcolor('yellow')    . $sKey. $oCli->getcolor('white'))
        : ($oCli->getcolor('dark gray') . $sKey. $oCli->getcolor('dark gray'))
    ).$sText
    ;
}

/**
 * Show a list of items
 * @param array $aItems
 * @return void
 */
function _listItems(array $aItems=[]): void{
    global $oCli;
    if(count($aItems)){
        $oCli->color('cli');
        foreach($aItems as $sItem){
            echo "  $sItem".PHP_EOL;
        }
        $oCli->color('reset');
    } else {
        $oCli->color('error', "No entry was found.".PHP_EOL);        
    }
    echo PHP_EOL;
}

/**
 * Show a list of users or groups and enter a selection
 * with tab-completion
 * 
 * @param string  $sPrefix  Prefix which will be shown when entering value
 * @param array   $aItems   array of items to show
 * @param string  $bAutoselectSingleItem  flag: if there is only one item, automatically select it
 * @param bool    $bForeceInput           flag: force input even if there is no item
 */
function _selectItem(string $sPrefix, array $aItems, bool $bAutoselectSingleItem=true, $bForeceInput=false){
    global $oCli;
    _listItems($aItems);
    if($bForeceInput){
        return trim($oCli->_cliInput("$sPrefix : ", ""));
    }
    switch (count($aItems)) {
        case 0:
            $sReturn='';
            break;
        case 1:
            if ($bAutoselectSingleItem){
                $oCli->color('info', "Info: There is only one item available - using '$aItems[0]'... ".PHP_EOL);        
                $sReturn=$aItems[0];
                break;
            }
        default:
            $oCli->setCompletions($aItems);
            $sReturn=trim($oCli->_cliInput("$sPrefix : ", ""));
            break;
    }    
    return $sReturn;
}

/**
 * Show a list of users and enter a selection with tab-completion
 * @param bool   $bAutoselectSingleUser  flag: if there is only one item, automatically select it
 * @param string $sPrefix                Visible prefix text when entering a username
 */
function _selectUser(bool $bAutoselectSingleUser=true, string $sPrefix='Username', $bForeceInput=false){
    global $oCli, $oHtpasswd;
    $oCli->color('reset', "Existing users:".PHP_EOL);        
    return _selectItem($sPrefix, array_keys($oHtpasswd->list()), $bAutoselectSingleUser, $bForeceInput);
}

/**
 * Show a list of groups and enter a selection with tab-completion
 * @param bool   $bAutoselectSingleGroup  flag: if there is only one item, automatically select it
 * @param string $sPrefix                 Visible prefix text when entering a username
 */
function _selectGroup(bool $bAutoselectSingleGroup=true, string $sPrefix='Groupname', $bForeceInput=false){
    global $oCli, $oHtgroup;
    $oCli->color('reset', "Existing groups:".PHP_EOL);        
    return _selectItem($sPrefix, $oHtgroup->list(), $bAutoselectSingleGroup, $bForeceInput);
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

if ($oCli->getvalue("debug")){
    $bDebugActions=true;
}

$FOLDER=$oCli->getvalue("folder")?:$FOLDER;
if(!is_dir($FOLDER)){
    $oCli->color('error');
    echo "ERROR: Folder '$FOLDER' doesn't exist.".PHP_EOL.PHP_EOL;
    $oCli->color('reset');
    exit(1);
}

while(true){

    // refresh user and group count
    $oHtgroup=new axelhahn\htgroup("$FOLDER/.htgroup");
    $oHtpasswd=new axelhahn\htpasswd("$FOLDER/.htpasswd");
    $iUserCount=count($oHtpasswd->list());
    $iGroupCount=count($oHtgroup->list());

    // clear screen
    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
    showheader();

    $oCli->color('info', "  Folder: '$FOLDER'".PHP_EOL
    ."    users : $iUserCount\n    groups: $iGroupCount\n\n"
    );

    $sReset=$oCli->getcolor('reset');

    $sFormat="  %-38s $sReset:  %-38s".PHP_EOL;
    $sFormatSingle="  %-38s $sReset".PHP_EOL;
    $sDelim="$sReset:  ";

    // show menu items

    h2("Menu");
    printf($sFormat, 
        _m(file_exists($oHtpasswd->getFile()), "s" , "show .htpasswd"),
        _m(file_exists($oHtgroup->getFile()), "S", "show .htgroup")
    );
    echo PHP_EOL;
    printf($sFormat, 
        _m(true, "a", "Add user"), 
        _m(true, "A", "Add group")
    );
    printf($sFormat, 
        _m($iUserCount, "l", "List users"), 
        _m($iGroupCount, "L", "List groups")
    );
    printf($sFormat, 
        _m($iUserCount, "r", "Remove a user"), 
        _m($iGroupCount, "R", "Remove group")
    );
    printf($sFormat, 
        _m($iUserCount, "p","Set a new password"), 
        _m(($iUserCount && $iGroupCount), "U","Add user to group")
    );
    printf($sFormat, 
        _m(true, "", ""), 
        _m(($iUserCount && $iGroupCount), "D", "Delete user from group")
    );

    echo PHP_EOL;
    printf($sFormatSingle, _m(($iUserCount || $iGroupCount), "v", "Verify groups and members"));
    printf($sFormatSingle, _m(true, "f", "Set another folder"));
    // printf($sFormatSingle, _m(($iUserCount && $iGroupCount), "v", "Verify groups and members"));
    printf($sFormatSingle, _m(true, "q", "Quit program"));
    echo PHP_EOL;

    $sMenu=$oCli->_cliInput(" > ", "");

    switch ($sMenu) {
        case 'q':
            $oCli->color('info', "Bye.".PHP_EOL);
            echo PHP_EOL;
            exit(0);

        case 's':
            $sFile=$oHtpasswd->getFile();
            h2("File: $sFile", "Content of current user file");
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
            h2("File: $sFile", "Content of current group file");
            if(file_exists($sFile)){
                $oCli->color("cli");
                echo file_get_contents($sFile);
                $oCli->color("reset");
            } else {
                $oCli->color('error', "ERROR: File '$sFile' doesn't exist yet.".PHP_EOL);
            }
            break;

        case 'a':
            h2("Add user", "Add a new user in ".basename($oHtpasswd->getFile()));
            $sUser=_selectUser(false, "New user", true);
            // $sUser=$oCli->_cliInput("Username : ", "");
            if ($sUser) {
                if(!$oHtpasswd->exists($sUser)){
                    
                    $sPW=$oCli->_cliInput("Password : ", "");
                    if ($sPW) {
                        $oHtpasswd->debug($bDebugActions);
                        $bSuccess=$oHtpasswd->add($sUser, $sPW);
                        $oHtpasswd->debug(false);
                        if($bSuccess){
                            $oCli->color('ok', "OK, user was added.".PHP_EOL);
                        } else {
                            $oCli->color('error', "Action failed.".PHP_EOL.$oHtpasswd->error().PHP_EOL);
                        }
                    } else {
                        $oCli->color('error', "Doing nothing - password is empty.".PHP_EOL);
                    }
                } else {
                    $oCli->color('error', "User already exists.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'A':
            h2("Add group", "Add a new group in ".basename($oHtgroup->getFile()));
            $sGroup=_selectGroup(false, "New Group", true);
            if ($sGroup) {
                if(!$oHtgroup->exists($sGroup)){
                    
                    $oHtgroup->debug($bDebugActions);
                    $bSuccess=$oHtgroup->add($sGroup);
                    $oHtgroup->debug(false);
                    if($bSuccess){
                        $oCli->color('ok', "OK, group was added.".PHP_EOL);
                    } else {
                        $oCli->color('error', "Action failed.".PHP_EOL.$oHtgroup->error().PHP_EOL);
                    }
                } else {
                    $oCli->color('error', "Group already exists.".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'l':
            h2("List users", "Current users and their groups");
            $oCli->color('cli');
            foreach(array_keys($oHtpasswd->list()) as $sUser){
                $sGroups="";
                foreach ($oHtgroup->list() as $sGroup){
                    if (in_array($sUser, $oHtgroup->members($sGroup))) {
                        $sGroups.=($sGroups ? ", ": "") . "$sGroup";
                    }
                }
                printf("  %-10s %s\n", "$sUser", $sGroups ? "👥 $sGroups": "(no group)");
            }
            if(count($oHtpasswd->list())==0){
                $oCli->color('error', "No users found.".PHP_EOL);
            }
            $oCli->color('reset');
            break;

        case 'L':
            h2("List groups", "Current groups and their members");
            $oCli->color('cli');
            foreach ($oHtgroup->list() as $sGroup){
                $aMembers=$oHtgroup->members($sGroup)??[];
                printf("  %-10s %s\n\n", "👥 $sGroup", count($aMembers) ? "\n     👤 " . implode("\n     👤 ", $aMembers): "(no members)");
            }
            if(count($oHtgroup->list())==0){
                $oCli->color('error', "No group was found.".PHP_EOL);
            }
            break;

        case 'r':
            h2("Remove a user", "Remove a user from ".basename($oHtpasswd->getFile()));
            $sUser=_selectUser(false, "Usename to remove");
            if($sUser){
                $sGroups="";
                foreach ($oHtgroup->list() as $sGroup){
                    if (in_array($sUser, $oHtgroup->members($sGroup))) {
                        $sGroups.=($sGroups ? ", ": "") . "$sGroup";
                    }
                }
                if($sGroups){
                    $oCli->color('error', "Aborting. Remove user from these groups first: $sGroups.".PHP_EOL);
                } else {
                    $oHtpasswd->debug($bDebugActions);
                    $bSuccess=$oHtpasswd->remove($sUser);
                    $oHtpasswd->debug(false);
                    if($bSuccess){
                        $oCli->color('ok', "OK, user was removed.".PHP_EOL);
                    } else {
                        $oCli->color('error', "Action failed.".PHP_EOL.$oHtpasswd->error().PHP_EOL);
                    }
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'R':
            h2("Remove a group", "Remove a group from ".basename($oHtgroup->getFile())."\n  WARNING: This function is dangerous. Check its members first!");
            $sGroup=_selectGroup(false, "Group to remove");
            if($sGroup){
                $oHtgroup->debug($bDebugActions);
                $bSuccess=$oHtgroup->remove($sGroup);
                $oHtgroup->debug(false);
                if($bSuccess){
                    $oCli->color('ok', "OK, group was removed.".PHP_EOL);
                } else {
                    $oCli->color('error', "Action failed.".PHP_EOL.$oHtgroup->error().PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'p':
            h2("Set password", "Set a new password for a selected user");
            $sUser=_selectUser(true);
            if($sUser){
                $sPW=trim($oCli->_cliInput("New password : ", ""));
                if($sPW){
                    $oHtpasswd->debug($bDebugActions);
                    $bSuccess=$oHtpasswd->update($sUser, $sPW);
                    $oHtpasswd->debug(false);
                    if($bSuccess){
                        $oCli->color('ok', "OK, password was changed.".PHP_EOL);
                    } else {
                        $oCli->color('error', "Action failed.".PHP_EOL.$oHtpasswd->error().PHP_EOL);
                    }
                } else {
                    $oCli->color('error', "Doing nothing. No password was set".PHP_EOL);
                }
            } else {
                $oCli->color('error', "Doing nothing.".PHP_EOL);
            }
            break;

        case 'U':
            h2("Add user to group", "Select a user and add it to an existing group");
            $sUser=_selectUser(true);
            if($sUser){
                if($oHtpasswd->exists($sUser)){
                    $sGroup=_selectGroup(true);
                    if($sGroup){
                        $oHtgroup->debug($bDebugActions);
                        $bSuccess=$oHtgroup->userAdd($sUser, $sGroup);
                        $oHtgroup->debug(false);
                        if($bSuccess){
                            $oCli->color('ok', "OK, user was added to the group.".PHP_EOL);
                        } else {
                            $oCli->color('error', "Action failed.".PHP_EOL.$oHtgroup->error().PHP_EOL);
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
            h2("Delete a user from a group", "Select a group and then one of its members to delete");
            $sGroup=_selectGroup(true);
            if($sGroup){
                if($oHtgroup->exists($sGroup)){
                    $oCli->color('reset', PHP_EOL);
                    echo "Members of goup '$sGroup':".PHP_EOL;
                    $oCli->color('cli');
                    $bUserfound=false;
                    foreach($oHtgroup->members($sGroup) as $sUser){
                        echo "  $sUser".PHP_EOL;
                        $bUserfound=true;
                    }
                    if(!$bUserfound){
                        $oCli->color('error', "Group '$sGroup' has no members.".PHP_EOL);
                    } else {
                        echo PHP_EOL;
                        $sUser=$oCli->_cliInput("Username : ", "");
                        if($sUser){
                            $oHtgroup->debug($bDebugActions);
                            $bSuccess=$oHtgroup->userRemove($sUser, $sGroup);
                            $oHtgroup->debug(false);
                            if($bSuccess){
                                $oCli->color('ok', "OK, user was removed from the group.".PHP_EOL);
                            } else {
                                $oCli->color('error', "Action failed.".PHP_EOL.$oHtgroup->error().PHP_EOL);
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

        case 'v':
            h2("Verify", "Check users and groups");
            $aWarnings=[];
            $aErrors=[];
            foreach ($oHtgroup->list() as $sGroup){
                $aMembers=$oHtgroup->members($sGroup)??[];
                if(count($aMembers)==0){
                    $aWarnings[]="Group '$sGroup' has no member.";
                } else {
                    foreach($aMembers as $sUser){
                        if(!$oHtpasswd->exists($sUser)){
                            $aErrors[]="Group '$sGroup' has the invalid member '$sUser'.";
                        }
                    }
                }
            }

            // if minimum one group exists check if all users have a group
            if($oHtgroup->list()){
                foreach(array_keys($oHtpasswd->list()) as $sUser){
                    $sGroups="";
                    foreach ($oHtgroup->list() as $sGroup){
                        if (in_array($sUser, $oHtgroup->members($sGroup))) {
                            $sGroups.="$sGroup, ";
                        }
                    }
                    if(!$sGroups){
                        $aWarnings[]="User '$sUser' has no group.";
                    }
                }
            }
            echo "Errors  : ".count($aErrors).PHP_EOL;
            echo "Warnings: ".count($aWarnings).PHP_EOL;
            echo PHP_EOL;
            if(count($aErrors)){
                echo "Errors: ".PHP_EOL;
                $oCli->color('error', "- " . implode("\n- ", $aErrors).PHP_EOL);
                echo PHP_EOL;
            }
            if(count($aWarnings)){
                echo "Warnings: ".PHP_EOL;
                $oCli->color('warning', "- " . implode("\n- ", $aWarnings).PHP_EOL);
                echo PHP_EOL;
            }

            if(!count($aErrors) && !count($aWarnings)){
                $oCli->color('ok', "OK, everything is fine.".PHP_EOL);
            }
            break;

        case 'f':
            h2("Set a new folder", "Define another folder to work with - or ose --folder param");
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
