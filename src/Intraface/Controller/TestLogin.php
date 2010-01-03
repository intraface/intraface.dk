<?php
/**
 * Logout
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_TestLogin extends k_Component
{
    protected $template;
    protected $kernel;

    function execute()
    {
        $this->url_state->init("continue", $this->url('/restricted/module'));
        return parent::execute();
    }

    function renderHtml()
    {
        $user = $this->selectUser('start@intraface.dk', 'startup');
        if ($user) {
            $this->session()->set('intraface_identity', $user);
            return new k_SeeOther($this->query('continue'));
        }

        return new k_SeeOther($this->url('../restricted'));
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
            $this->session()->set('intraface_identity', $user);
            return new k_SeeOther($this->query('continue'));
        }
        return $this->render();
    }

    protected function selectUser($username, $password)
    {
        $adapter = new Intraface_Auth_User(MDB2::singleton(DB_DSN), $this->session()->sessionId(), $username, $password);

        $auth = new Intraface_Auth($this->session()->sessionId());
        $auth->attachObserver(new Intraface_Log);

        $auth->authenticate($adapter);

        if (!$auth->hasIdentity()) {
            throw new Exception('Could not login with those credentials');
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
        return new Intraface_AuthenticatedUser($username, $this->language());
    }
}