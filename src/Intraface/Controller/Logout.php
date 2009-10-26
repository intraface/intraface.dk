<?php
/**
 * Logout
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_Logout extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function execute() {
        $this->url_state->init("continue", $this->url('/login'));
        return parent::execute();
    }

    function GET()
    {
        if ($this->getAuth()->clearIdentity()) {
            $this->session()->set('identity', null);
            return new k_SeeOther($this->query('continue'));
        } else {
            throw new Exception('Could not logout');
        }
        return parent::GET();
    }

    function getAuth()
    {
		return $this->context->getAuth();
    }
}