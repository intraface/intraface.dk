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

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function GET()
    {
        if ($this->getAuth()->clearIdentity()) {
            return k_SeeOther($this->url('../login'));
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

