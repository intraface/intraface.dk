<?php
class Intraface_modules_newsletter_Controller_Letters extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_Letter';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = new k_Template(dirname(__FILE__) . '/templates/letters.tpl.php');
        return $smarty->render($this);
    }

    function DELETE()
    {
        $letter = new Newsletter($list, $_GET['delete']);
        $letter->delete();
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

        $smarty = new k_Template(dirname(__FILE__) . '/templates/letter-edit.tpl.php');
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

    function t($phrase)
    {
         return $phrase;
    }
}