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
    protected $template;
    protected $kernel;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function execute()
    {
        $this->url_state->init("continue", $this->url('/restricted'));
        return parent::execute();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/login.tpl.php');
        return $smarty->render($this);
    }

    /*
    function getKernel()
    {
        if (is_object($this->kernel)) {
            return $this->kernel;
        }
    	return $this->kernel = $this->registry->get('kernel');
    }
    */

    function postForm()
    {
        $user = $this->selectUser($this->body('email'), $this->body('password'));
        if ($user) {
            $this->session()->set('identity', $user);
            return new k_SeeOther($this->query('continue'));
        } else {
            return new k_SeeOther($this->url(null, array('flare' => 'Wrong credentials')));
        }
        return $this->render();
    }

    protected function selectUser($username, $password)
    {
        $adapter = new Intraface_Auth_User(MDB2::singleton(DB_DSN), session_id(), $username, $password);

        $auth = new Intraface_Auth(session_id());
        $auth->attachObserver(new Intraface_Log);

        $auth->authenticate($adapter);

        if (!$auth->hasIdentity()) {
            return false;
        }

        /*
        $this->getKernel()->user = $auth->getIdentity(MDB2::singleton(DB_DSN));

        if (!$intranet_id = $this->getKernel()->user->getActiveIntranetId()) {
            throw new Exception('no active intranet_id');
        }

        $this->getKernel()->intranet = new Intraface_Intranet($intranet_id);

        // @todo why are we setting the id?
        $this->getKernel()->user->setIntranetId($this->getKernel()->intranet->get('id'));
        $this->getKernel()->setting = new Intraface_Setting($this->getKernel()->intranet->get('id'), $this->getKernel()->user->get('id'));

        $this->session()->set('kernel', $this->getKernel());
        $this->session()->set('user', $this->getKernel()->user);
        $this->session()->set('intranet', $this->getKernel()->intranet);
		*/

        return new k_AuthenticatedUser($username);
    }

    function t($phrase)
    {
        return $phrase;
    }
}