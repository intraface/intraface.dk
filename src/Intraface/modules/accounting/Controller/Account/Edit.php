<?php
class Intraface_modules_accounting_Controller_Account_Edit extends k_Component
{
    function renderHtml()
    {
        $this->document->setTitle('Edit account');

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/account/edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year, $this->name());

        if (isset($_POST['vat_key']) && $_POST['vat_key'] != 0) {
            $_POST['vat_percent'] = 25;
        }

        if ($id = $account->save($_POST)) {
            return new k_SeeOther($this->url());
        } else {
            $values = $_POST;
        }
        return $this->render();
        /*
        if ($id = $this->getYear()->save($_POST)) {
            return new k_SeeOther($this->url('../'));
        } else {
            $values = $_POST;
            $values['from_date_dk'] = $_POST['from_date'];
            $values['to_date_dk'] = $_POST['to_date'];
            return $this->render();
        }
        */
    }

    function GET()
    {
        if (!empty($this->name()) AND is_numeric($this->name())) {
        	$account = $this->getYear($this->name());
        	$values = $account->get();
        } else {
        	$account = $this->getYear();
        	$values = array();
        }
        parent::GET();
    }

    function getAccount($id)
    {
        return $this->context->getAccount($id);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
         if (!is_numeric($this->name())) {
        	return new Year($this->getKernel());
        } else {
        	return new Year ($this->getKernel(), $this->name());
        }
    }

    function getYearGateway()
    {
        return $this->context->getYearGateway();
    }
}