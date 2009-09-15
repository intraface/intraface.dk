<?php
/**
 * Logout
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_Login extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/login.tpl.php');
        return $smarty->render($this);
    }

    function POST()
    {
    	$adapter = new Intraface_Auth_User(MDB2::singleton(DB_DSN), session_id(), $_POST['email'], $_POST['password']);

        $auth = new Intraface_Auth(session_id());
        $auth->attachObserver(new Intraface_Log);

        $user = $auth->authenticate($adapter);

    	if (is_object($user)) {
    	    return new k_SeeOther($this->url('../'));
        } else {
    		$msg = 'wrong credentials';
        }
    }
}