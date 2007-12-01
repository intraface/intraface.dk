<?php
class Intraface_Tools_Controller_Root extends k_Dispatcher
{
    public $map = array('tools' => 'Intraface_Tools_Controller_Index',
                        'login' => 'Intraface_Tools_Controller_Login');

    function __construct()
    {
        parent::__construct();
        $this->document->template = dirname(__FILE__) . '/../tpl/main-tpl.php';
    }

    function execute()
    {
        return $this->forward('tools');
    }

    function loadUser($username, $password)
    {
        $liveuser = new Tools_User;
        $liveuser->login($username, $password);
        return $liveuser;
    }

    function forward($name)
    {
        if ($name == 'login') {
            return parent::forward('login');
        }

        if (!$this->registry->get('user')->isLoggedIn()) {
            throw new k_http_Redirect($this->url('login'));
        }
        return parent::forward('tools');
    }

}