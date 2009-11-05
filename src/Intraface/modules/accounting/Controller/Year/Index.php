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

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return new Year($this->getKernel());
    }

    function getValues()
    {
        $values['from_date_dk'] = '01-01-' . date('Y');
        $values['to_date_dk'] = '31-12-' . date('Y');
        return $values;
    }

    function postForm()
    {

        $module = $this->getKernel()->module('accounting');
        // her sætter vi et år
        if (!empty($_POST['id']) AND is_numeric($_POST['id'])) {
        	$year = new Year($this->getKernel(), $_POST['id']);

        	if (!$year->setYear()) {
        		throw new Exception('Could not set the year');
        	}
        	return new k_SeeOther($this->url());
        }

        if ($id = $this->getYear()->save($_POST)) {
            return new k_SeeOther($this->url($id));
        }
        $values = $_POST;
        $values['from_date_dk'] = $_POST['from_date'];
        $values['to_date_dk'] = $_POST['to_date'];
        return $this->render();
    }

    function renderHtmlCreate()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/edit.tpl.php');
        return $smarty->render($this);
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }

}