<?php
class Intraface_modules_accounting_Controller_Year_Edit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/edit');
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
        return $this->context->getKernel();
    }

    function getYear()
    {
        if (is_numeric($this->context->name())) {
            return new Year($this->getKernel(), $this->context->name());
        } else {
            return new Year($this->getKernel(), 0, false);
        }
    }

    function getYearGateway()
    {
        return $this->context->getYearGateway();
    }

    function getValues()
    {
        return $this->getYear()->get();
    }
}
