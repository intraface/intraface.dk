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
        } elseif ($name == 'account') {
            return 'Intraface_modules_accounting_Controller_Account_Index';
        } elseif ($name == 'voucher') {
            return 'Intraface_modules_accounting_Controller_Voucher_Index';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        return new k_SeeOther(PATH_WWW."modules/accounting/index_old.php");
        if ($this->getYear()->getId() > 0) {
            return new k_SeeOther($this->url('daybook'));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $this->getHeader() . $smarty->render($this) . $this->getFooter();
    }

    function getKernel()
    {
        if (method_exists('getKernel', $this->context)) {
             return $this->context->getKernel();
        }
        $registry = $this->registry->create();
    	return $registry->get('kernel');
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return new Year($this->getKernel());
    }

    function getYearGateway()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

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

    function getPage()
    {
        $registry = $this->registry->create();
    	return $registry->get('page');
    }

    function getHeader()
    {
        ob_start();
        $this->getPage()->start('Newsletter');
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function getFooter()
    {
        ob_start();
        $this->getPage()->end();
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function wrapHtml($content)
    {
        $scripts = '';
        foreach ($this->document->scripts() as $script) {
            $scripts .= '<script src="'.$script.'" type="text/javascript"></script>';
        }
        return $this->getHeader() . $scripts . $content . $this->getFooter();
    }
}