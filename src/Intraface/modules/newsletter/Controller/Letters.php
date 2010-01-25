<?php
class Intraface_modules_newsletter_Controller_Letters extends k_Component
{
    protected $template;

    function getValues()
    {
        return array();
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_Letter';
        }
    }

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/letters');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getList()
    {
        return $this->context->getList();
    }

    function getLetter()
    {
        return new Newsletter($this->context->getList());
    }

    function getLetters()
    {
        $this->getKernel()->module('newsletter');
        $translation = $this->getKernel()->getTranslation('newsletter');

        return $this->getLetter()->getList();
    }

    function renderHtmlNew()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/letter-edit');
        return $smarty->render($this);
    }

    function postForm()
    {
    	$module = $this->getKernel()->module("newsletter");
        $letter = new Newsletter($this->getList());

    	if ($id = $letter->save($_POST)) {
    		return new k_SeeOther($this->url($id));
    	} else {
    		$value = $_POST;
    	}
    	return $this->render();
    }
}