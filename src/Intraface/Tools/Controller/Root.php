<?php
class Intraface_Tools_Controller_Root extends k_Component
{
    private $user;
    public $map = array('errorlist'   => 'Ilib_ErrorHandler_Observer_File_ErrorList_Controller_Index',
                        'phpinfo'     => 'Intraface_Tools_Controller_Phpinfo',
                        'log'         => 'Intraface_Tools_Controller_Log',
                        'translation' => 'Translation2_Frontend_Controller_Index'
                );
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        return 'dashboard';
    }

  function dispatch() {
    if ($this->identity()->anonymous()) {
      throw new k_NotAuthorized();
    }
    return parent::dispatch();
  }

    function map($name)
    {
        return $this->map[$name];
    }

    function wrapHtml($content)
    {
        $navigation = array(
            $this->url('translation') => 'Translations',
            $this->url('phpinfo') => 'PHP info',
            $this->url('errorlist') => 'All errors',
            $this->url('log') => 'Log'
        );

        $tpl = $this->template->create('wrapper');
        return $tpl->render($this, array('navigation' => $navigation)) . $content;
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function getTranslationCommonPageId()
    {
        return 'common';
    }
}