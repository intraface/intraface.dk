<?php
class Intraface_modules_accounting_Controller_Account_Show extends k_Component
{
    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Account_Edit';
        }
    }

    function renderHtml()
    {
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
        return $smarty->render($this, array('posts' => $posts));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        $year = new Year($this->getKernel());
        $year->checkYear();
        return $year;
    }

    function renderHtmlEdit()
    {
        $this->document->addScript($this->url('/../accounting/javascript/edit_account.js'));

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
    }

    function getValues()
    {
        return $this->getAccount()->get();;
    }

    function getAccount()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');
        return new Account($this->getYear(), $this->name());
    }
}