<?php
class Intraface_modules_accounting_Controller_Year_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_accounting_Controller_Year_Edit';
        } elseif (is_numeric($name)) {
        	return 'Intraface_modules_accounting_Controller_Year_Show';
        }
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/index');
        return $smarty->render($this);
    }

    function putForm()
    {
        $year = new Year($this->getKernel(), $_POST['id']);

        if (!$year->setYear()) {
        	throw new Exception('Could not set the year');
        }
        return new k_SeeOther($this->url($year->getId()));
    }

    function postForm()
    {
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
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/edit');
        return $smarty->render($this);
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

    function getYear()
    {
        return new Year($this->getKernel());
    }

    function getValues()
    {
        $values['from_date_dk'] = '01-01-' . date('Y');
        $values['to_date_dk'] = '31-12-' . date('Y');
        return $values;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}