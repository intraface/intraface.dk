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
    protected $template;
    protected $kernel;
    protected $auth;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, Intraface_Auth $auth, MDB2_Driver_Common $mdb2, Intraface_Log $log)
    {
        $this->template = $template;
        $this->auth = $auth;
        $this->mdb2 = $mdb2;
        $this->log = $log;
    }

    function execute()
    {
        $this->url_state->init("continue", $this->url('/restricted'));
        return parent::execute();
    }

    function renderHtml()
    {
        $this->document->setTitle('Login');
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/login');
        return $smarty->render($this);
    }

    function postForm()
    {
        $user = $this->selectUser($this->body('email'), $this->body('password'));
        if ($user) {
            $this->session()->set('intraface_identity', $user);
            return new k_SeeOther($this->query('continue'));
        } else {
            return new k_SeeOther($this->url(null, array('flare' => 'Wrong credentials')));
        }
        return $this->render();
    }

    protected function selectUser($username, $password)
    {
        $adapter = new Intraface_Auth_User($this->mdb2, session_id(), $username, $password);

        $this->auth->attachObserver($this->log);
        $this->auth->authenticate($adapter);

        if (!$this->auth->hasIdentity()) {
            return false;
        }

        return new Intraface_AuthenticatedUser($username, $this->language());
    }
}