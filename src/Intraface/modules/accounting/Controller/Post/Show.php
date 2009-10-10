<?php
class Intraface_modules_accounting_Controller_Post_Show extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Post_Edit';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        if (!$this->getYear()->isValid()) {
            trigger_error('Året er ikke gyldigt', E_USER_ERROR);
        }

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/post/show.tpl.php');
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


    function POST()
    {
        if (!empty($_POST['start']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            header('Location: daybook.php');
            exit;
        }
        if (!empty($_POST['primobalance']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            header('Location: primosaldo.php');
            exit;
        } elseif (!empty($_POST['manual_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            header('Location: accounts.php');
            exit;
        } elseif (!empty($_POST['standard_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $this->getYear()->setYear();
            if (!$this->getYear()->createAccounts('standard')) {
                trigger_error('Kunne ikke oprette standardkontoplanen', E_USER_ERROR);
            }

            $values = $this->getYear()->get();

            return new k_SeeOther($this->url());

        } elseif (!empty($_POST['transfer_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            // kontoplanen fra sidste år hentes
            $year = $this->getYear();
            $year->setYear();
            if (empty($_POST['accountplan_year']) OR !is_numeric($_POST['accountplan_year'])) {
                $year->error->set('Du kan ikke oprette kontoplanen, for du har ikke valgt et år at gøre det fra');
            }
            else {
                if (!$year->createAccounts('transfer_from_last_year', $_POST['accountplan_year'])) {
                    trigger_error('Kunne ikke oprette standardkontoplanen', E_USER_ERROR);
                }
            }
            $values = $year->get();
        }


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
    	return new VatPeriod($$this->getYear());
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }

}