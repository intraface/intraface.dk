<?php
class Intraface_modules_accounting_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    protected function map($name)
    {
        if ($name == 'year') {
            return 'Intraface_modules_accounting_Controller_Year_Index';
        } elseif ($name == 'daybook') {
        	return 'Intraface_modules_accounting_Controller_Daybook';
        } elseif ($name == 'settings') {
            return 'Intraface_modules_accounting_Controller_Settings';
        } elseif ($name == 'account') {
            return 'Intraface_modules_accounting_Controller_Account_Index';
        } elseif ($name == 'voucher') {
            return 'Intraface_modules_accounting_Controller_Voucher_Index';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        if ($this->getYear()->getId() > 0) {
            return new k_SeeOther($this->url('daybook'));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
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

    function wrapHtml($content)
    {
        $scripts = '';
        foreach ($this->document->scripts() as $script) {
            $scripts .= '<script src="'.$script.'" type="text/javascript"></script>';
        }
        return $scripts . $content;
        //return $this->getHeader() . $scripts . $content . $this->getFooter();
    }
}