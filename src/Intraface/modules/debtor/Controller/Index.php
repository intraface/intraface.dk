<?php
class Intraface_modules_debtor_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function map($name)
    {
        if ($name == 'list') {
            return 'Intraface_modules_debtor_Controller_Collection';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module("debtor");
        $translation = $this->getKernel()->getTranslation('contact');

        if ($this->getKernel()->user->hasModuleAccess("invoice")) {
            return new k_SeeOther($this->url('list', array('type' => 'invoice')));
        } elseif ($this->getKernel()->user->hasModuleAccess("order")) {
            return new k_SeeOther($this->url('list', array('type' => 'order')));
        } elseif ($this->getKernel()->user->hasModulesAccess("quotation")) {
	        return new k_SeeOther($this->url('list', array('type' => 'quotation')));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLists()
    {
        $list = new NewsletterList($this->getKernel());
        return $list->getList();
    }

    function t($phrase)
    {
         return $phrase;
    }
}