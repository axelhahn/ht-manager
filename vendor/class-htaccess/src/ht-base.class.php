<?php

namespace axelhahn;

/**
 * ======================================================================
 * 
 * PHP base class for handling ht* file
 * 
 * @author www.axel-hahn.de
 * @license GNU Public License 3.0
 * @source https://github.com/axelhahn/php-htpasswd/
 * 
 * ----------------------------------------------------------------------
 * 2025-08-12  initial version
 * ======================================================================
 */
class htbase
{

    // ----------------------------------------------------------------------
    // vars
    // ----------------------------------------------------------------------

    /**
     * Last error
     * @var string
     */
    protected string $sLastError = '';

    /**
     * Flag: show debug infos?
     * @var bool
     */
    protected bool $bDebug = false;

    // ----------------------------------------------------------------------
    // constructor
    // ----------------------------------------------------------------------

    // ----------------------------------------------------------------------
    // methods
    // ----------------------------------------------------------------------

    /**
     * Write debug info; only if debugging was activated
     * 
     * @see debug()
     * 
     * @param string $sMessage
     * @return void
     */
    protected function _wd(string $sMessage): void
    {
        if ($this->bDebug) {
            echo "DEBUG: $sMessage" . PHP_EOL;
        }
    }

    /**
     * Write debug info on error and store last error message
     * 
     * @see debug()
     * 
     * @param string $sMessage
     * @return void
     */
    protected function _error(string $sMessage): void
    {
        $this->_wd("ERROR $sMessage");
        $this->sLastError = $sMessage;
    }

    /**
     * Enable or disable debug mode
     * 
     * @param bool $bDebug  new value of debug flag
     * @return void
     */
    public function debug(bool $bDebug): void
    {
        $this->bDebug = $bDebug;
    }

    /**
     * Get the last error
     * 
     * @return string
     */
    public function error(): string
    {
        return $this->sLastError;
    }

}