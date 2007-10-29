<?php

class Intraface_Tools_Controller_Root extends k_Dispatcher
{
    public $map = array('tools' => 'Intraface_Tools_Controller_Index');

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

    function handleRequest()
    {
        if (!$this->registry->identity->isLoggedIn()) {
            throw new k_http_Authenticate();
        }
        return parent::handleRequest();
    }

}