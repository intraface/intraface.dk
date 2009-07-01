<?php
class Intraface_modules_accounting_Controller_Year_Index extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_accounting_Controller_Year_Edit';
        } elseif (is_numeric($name)) {
        	return 'Intraface_modules_accounting_Controller_Year_Show';
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
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear($id = 0)
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return new Year($this->getKernel(), $id);
    }

    function POST()
    {
        $year = $this->getYear($_POST['id']);
        if ($year->setYear()) {
            return new k_SeeOther($this->url('../daybook'));
        }
        return $this->render();
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }

}