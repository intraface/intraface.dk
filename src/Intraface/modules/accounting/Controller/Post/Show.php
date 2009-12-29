<?php
class Intraface_modules_accounting_Controller_Post_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Post_Edit';
        }
    }

    function renderHtml()
    {
        if (!$this->getYear()->isValid()) {
            throw new Exception('Year id ' .$this->getYear()->getId().  ' is not valid');
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/post/show');
        return $smarty->render($this);
    }

    function postForm()
    {
        if (!empty($_POST['start']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();

            return k_SeeOther($this->url('../../daybook'));
        }
        if (!empty($_POST['primobalance']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();

            return k_SeeOther($this->url('../../primosaldo'));
        } elseif (!empty($_POST['manual_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $year = $this->getYear();
            $year->setYear();
            return k_SeeOther($this->url('../../accounts'));

        } elseif (!empty($_POST['standard_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
            $this->getYear()->setYear();
            if (!$this->getYear()->createAccounts('standard')) {
                throw new Exception('Kunne ikke oprette standardkontoplanen', E_USER_ERROR);
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
                    throw new Exception('Kunne ikke oprette standardkontoplanen', E_USER_ERROR);
                }
            }
            $values = $year->get();
        }
        return $this->render();
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

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        return new Year($this->getKernel(), $this->name());
    }
}