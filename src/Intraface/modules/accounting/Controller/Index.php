<?php
class Intraface_modules_accounting_Controller_Index extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'year') {
            return 'Intraface_modules_accounting_Controller_Year_Index';
        } elseif ($name == 'daybook') {
        	return 'Intraface_modules_accounting_Controller_Daybook';
        } elseif ($name == 'settings') {
            return 'Intraface_modules_accounting_Controller_Settings';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }
/*
    function execute()
    {
        return $this->wrap(parent::execute());
    }
*/
    function renderHtml()
    {
        if ($this->getModel()->getId() > 0) {
            return new k_SeeOther($this->url('daybook'));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        $registry = $this->registry->create();
    	return $registry->get('kernel');
    }

    function getModel()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return new Year($this->getKernel());
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

}