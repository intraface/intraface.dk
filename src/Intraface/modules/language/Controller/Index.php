<?php
class Intraface_modules_language_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Languages');

        $gateway = new Intraface_modules_language_Gateway;

        $languages = new Intraface_modules_language_Languages;
        $chosen = $languages->getChosenAsArray();

        $data = array('languages' => $gateway->getAll(), 'chosen' => $chosen);

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/languages');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $languages = new Intraface_modules_language_Languages;
        $languages->flush();
        if ($this->body('language')) {
        	foreach ($this->body('language') as $key) {
                $languages = new Intraface_modules_language_Languages;
                $languages->type_key = $key;
                $languages->save();
        	}
        }
        return new k_SeeOther($this->url());
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/content');
        return $tpl->render($this, array('content' => $content));
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function document()
    {
        return $this->document;
    }

}