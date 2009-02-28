<?php
class Intraface_Tools_Controller_Root extends k_Dispatcher
{
    public $map = array('index' => 'Intraface_Tools_Controller_Index',
                        'login' => 'Intraface_Tools_Controller_Login');

    function __construct()
    {
        parent::__construct();
        $this->document->template = 'Intraface/Tools/templates/main-tpl.php';
        $this->document->navigation = array(
            $this->url('translation') => 'Translations',
            $this->url('phpinfo') => 'PHP info',
            $this->url('errorlog/unique') => 'Unique errors (html)',
            $this->url('errorlog/all') => 'All errors',
            $this->url('errorlog/rss') => 'Errors as rss',
            $this->url('log') => 'Log'
        );
    }

    function execute()
    {
        return $this->forward('index');
    }

    function forward($name)
    {
        if ($name == 'login') {
            return parent::forward('login');
        }

        if (!$this->registry->get('user')->isLoggedIn()) {
            throw new k_http_Redirect($this->url('login'));
        }
        return parent::forward('index');
    }

}