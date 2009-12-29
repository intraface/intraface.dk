<?php
/**
 * Year
 *
 * @todo use this implementation for the active year
 *
 * @package Intraface_Accounting
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
class Intraface_modules_accouting_ActiveYear
{
    /**
     * @var object
     */
    protected $kernel;

    /**
     * Constructor
     *
     * @param $kernel
     *
     * @return void
     */
    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Sets the active year
     *
     * @return boolean
     */
    function set($year)
    {
        $this->kernel->setting->set('user', 'accounting.active_year', $year->getId());
        return true;
    }

    /**
     * Finds the ative year
     *
     * @todo should it return a year object? - probably not as I then have to put in the gateway?
     *
     * @return integer
     */
    function get()
    {
        return $this->kernel->setting->get('user', 'accounting.active_year');
    }

    /**
     * Resets the active year for a user
     *
     * @return boolean
     */
    private function reset()
    {
        $this->kernel->setting->set('user', 'accounting.active_year', 0);
        return true;
    }
}