<?php
class Intraface_modules_accounting_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        // make it possible to keep the menu, but have a proper controller hierarchy
        $next = array('daybook', 'search', 'voucher', 'account');

        if (in_array($this->next(), $next) and $this->next() != 'year') {
            return new k_SeeOther($this->url('year/' . $this->getYear()->get('id') . '/' . $this->next()));
        }

        return parent::dispatch();
    }

    protected function map($name)
    {
        if ($name == 'year') {
            return 'Intraface_modules_accounting_Controller_Year_Index';
        }
    }

    function renderHtml()
    {
        if ($this->getYear()->getId() > 0) {
            return new k_SeeOther($this->url('daybook'));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        return new Year($this->getKernel());
    }

    function getYearGateway()
    {
        return new Year($this->getKernel());
    }

    function getAccounts()
    {
        return $this->getAccount()->getList();
    }

    function getAccount($id = 0)
    {
        return new Account($this->getYear(), $id);
    }
}