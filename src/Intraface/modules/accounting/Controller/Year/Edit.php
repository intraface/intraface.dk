<?php
class Intraface_modules_accounting_Controller_Year_Edit extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        if ($id = $this->getYear()->save($_POST)) {
            return new k_SeeOther($this->url('../'));
        } else {
            $values = $_POST;
            $values['from_date_dk'] = $_POST['from_date'];
            $values['to_date_dk'] = $_POST['to_date'];
            return $this->render();
        }
    }

    function getKernel()
    {
        $registry = $this->registry->create();
        return $registry->get('kernel');
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        if (!is_numeric($this->name())) {
        	return new Year($this->getKernel());
        } else {
        	return new Year ($this->getKernel(), $this->name());
        }
    }

    function getYearGateway()
    {
        return $this->context->getYearGateway();
    }

}