<?php
class Intraface_modules_accounting_Controller_Account_Show extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Account_Edit';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year, (int)$this->name());

        $saldo = 0;
        $posts = array();
        // primosaldo
        $primo = $account->getPrimoSaldo();
        $posts[0]['id'] = '';
        $posts[0]['date'] = '';
        $posts[0]['voucher_number'] = '';
        $posts[0]['text'] = 'Primosaldo';
        $posts[0]['debet'] = $primo['debet'];
        $posts[0]['credit'] = $primo['credit'];
        $posts[0]['saldo'] = $primo['debet'] - $primo['credit'];

        $posts = array_merge($posts, $account->getPosts());

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/account/show.tpl.php');
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

    function getAccount($id = 0)
    {
    	return new Account($this->getYear(), $id);
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

    function t($phrase)
    {
        return $phrase;
    }
}