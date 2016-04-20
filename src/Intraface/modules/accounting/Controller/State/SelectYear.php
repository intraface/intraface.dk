<?php
class Intraface_modules_accounting_Controller_State_SelectYear extends Intraface_modules_accounting_Controller_Year_Index
{
    protected $year;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/year-not-ready');
            return $smarty->render($this);
        }
        return new k_SeeOther($this->url('../'));
    }

    function putForm()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');
        $year = new Year($this->getKernel(), $this->body('year_id'));
        if ($year->setYear()) {
            return new k_SeeOther($this->url('../'));
        }
        throw new Exception('Could not set the year');
    }

    function getYears()
    {
        return $this->getYear()->getList();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function getYear()
    {
        $accounting_module = $this->getKernel()->useModule('accounting');
        if (is_object($this->year)) {
            return $this->year;
        }

        $this->year = new Year($this->getKernel());
        $this->year->loadActiveYear();
        return $this->year;
    }

    function getModule()
    {
        return $accounting_module = $this->getKernel()->useModule('accounting');
    }
}
