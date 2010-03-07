<?php
class Intraface_modules_accounting_Controller_Year_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

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
        } elseif ($name == 'primosaldo') {
            return 'Intraface_modules_accounting_Controller_Year_Primosaldo';
        } elseif ($name == 'search') {
            return 'Intraface_modules_accounting_Controller_Search';
        }
    }

    function dispatch()
    {
        if ($this->getYear() == 0) {
            throw new k_PageNotFound();
        }
        if (!$this->getYear()->isValid()) {
            throw new Exception('Year is not valid');
        }

        return parent::dispatch();
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/show');
        return $smarty->render($this);
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
            return new k_SeeOther($this->url('primosaldo'));
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
        return $this->render();
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

    function getYear()
    {
        return $this->getYearGateway()->findById($this->name());
    }

    function getVoucherGateway()
    {
        return new Intraface_modules_accounting_VoucherGateway($this->getYear());
    }

    function getAccountGateway()
    {
        return new Intraface_modules_accounting_AccountGateway($this->getYear());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

}