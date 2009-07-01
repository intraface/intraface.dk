<?php
class Intraface_Tools_Controller_Root extends k_Dispatcher
{
    private $user;
    
    
    public $map = array('dashboard' => 'Ilib_SimpleLogin_Controller_Index',
                        'login' => 'Ilib_SimpleLogin_Controller_Login',
                        'logout' => 'Ilib_SimpleLogin_Controller_LogOut',
                        'errorlist'    => 'Ilib_ErrorHandler_Observer_File_ErrorList_Controller_Index',
                        'phpinfo'     => 'Intraface_Tools_Controller_Phpinfo',
                        'log'         => 'Intraface_Tools_Controller_Log',
                        'translation' => 'Translation2_Frontend_Controller_Index'
                        );
    
    function __construct()
    {
        parent::__construct();
        $this->document->template = 'Intraface/Tools/templates/main-tpl.php';
        $this->document->navigation = array(
            $this->url('translation') => 'Translations',
            $this->url('phpinfo') => 'PHP info',
            $this->url('errorlist/unique') => 'Unique errors (html)',
            $this->url('errorlist/all') => 'All errors',
            $this->url('errorlist/rss') => 'Errors as rss',
            $this->url('log') => 'Log',
            $this->url('logout') => 'Log out'
        );
    }
    
    function execute()
    {
        throw new k_http_Redirect($this->url('dashboard'));
    }
    
    public function forward($name)
    {
        $login_exceptions = array('login', 'errorlist/rss');
        
        if (!$this->getUser()->isLoggedIn() && !in_array($this->getSubspace(), $login_exceptions)) {
            throw new k_http_Redirect($this->url('login'));
        }
        
        return parent::forward($name);
    }
    
    function getTranslationCommonPageId()
    {
        return 'common';
    }
    
    function getUser()
    {
        if(empty($this->user)) {
            $this->user = $this->registry->get('user');
            $this->user->register('sune@intraface.dk', '7f5c04fb811783c71d951302e3314d62');
            $this->user->register('lars@legestue.net', '0294de2de14cc570a3242f22fe1311c5');
        }
        
        return $this->user;   
    }

}