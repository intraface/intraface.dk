<?php
class Intraface_modules_accounting_Controller_Year_Show extends k_Component
{
    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Year_Edit';
        } elseif ($name == 'daybook') {
            return 'Intraface_modules_accounting_Controller_Daybook';
        } elseif ($name == 'settings') {
            return 'Intraface_modules_accounting_Controller_Settings';
        } elseif ($name == 'account') {
            return 'Intraface_modules_accounting_Controller_Account_Index';
        } elseif ($name == 'vat') {
            return 'Intraface_modules_accounting_Controller_Vat_Index';
        } elseif ($name == 'voucher') {
            return 'Intraface_modules_accounting_Controller_Voucher_Index';
        } elseif ($name == 'end') {
            return 'Intraface_modules_accounting_Controller_Year_End';
        }
    }

    function renderHtml()
    {
        if (!$this->getYear()->isValid()) {
            throw new Exception('Year is not valid');
        }

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/show.tpl.php');
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

        return new Year($this->getKernel(), $this->name());
    }

    function postForm()
    {
        if (!empty($_POST['start']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return new k_SeeOther($this->url('daybook'));
        } elseif (!empty($_POST['primobalance']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return new k_SeeOther($this->url('daybook'));
        } elseif (!empty($_POST['manual_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return new k_SeeOther($this->url('account'));
        } elseif (!empty($_POST['standard_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $this->getYear()->setYear();
            if (!$this->getYear()->createAccounts('standard')) {
                throw new Exception('Kunne ikke oprette standardkontoplanen');
            }

            $values = $this->getYear()->get();
            return new k_SeeOther($this->url());

        } elseif (!empty($_POST['transfer_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            // kontoplanen fra sidste �r hentes
            $year = $this->getYear();
            $year->setYear();
            if (empty($_POST['accountplan_year']) OR !is_numeric($_POST['accountplan_year'])) {
                $year->error->set('Du kan ikke oprette kontoplanen, for du har ikke valgt et �r at g�re det fra');
            } else {
                if (!$year->createAccounts('transfer_from_last_year', $_POST['accountplan_year'])) {
                    throw new Exception('Kunne ikke oprette standardkontoplanen');
                }
            }
            $values = $year->get();
        }
    }

    function getValues()
    {
        return $this->getYear()->get();
    }

    function getYears()
    {
    	return $this->getYear()->getList();
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getVatPeriod()
    {
    	return new VatPeriod($this->getYear());
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }

    function t($phrase)
    {
        return $phrase;
    }
}