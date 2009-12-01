<?php
class Intraface_modules_todo_Controller_Setting extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('todo');
        $translation = $this->getKernel()->getTranslation('todo');

    	$value['publiclist'] = 	$kernel->setting->get('intranet','todo.publiclist');
    	$value['emailstandardtext'] = 	$kernel->setting->get('user','todo.email.standardtext');

        $data = array(
            'value' => $value
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/setting');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
    	$kernel->setting->set('intranet','todo.publiclist', $_POST['publiclist']);
    	$kernel->setting->set('user','todo.email.standardtext', $_POST['emailstandardtext']);
    	return new k_SeeOther($this->url('../'));
    }
}