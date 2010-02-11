<?php
class Intraface_modules_accounting_Controller_Account_Show extends k_Component
{
    protected $account;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $account = $this->getAccount();

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

        $this->document->setTitle('Account');

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/account/show');
        return $smarty->render($this, array('posts' => $posts, 'saldo' => $saldo));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtmlEdit()
    {
        $this->document->addScript($this->url('accounting/edit_account.js'));

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/account/edit');
        return $smarty->render($this);
    }

    function renderHtmlDelete()
    {
        $account = $this->getAccount();
        $account->delete();

        return new k_SeeOther($this->context->url());
    }

    function postForm()
    {
        $account = $this->getAccount();

        if (isset($_POST['vat_key']) && $_POST['vat_key'] != 0) {
            $_POST['vat_percent'] = 25;
        }

        if ($id = $account->save($_POST)) {
            return new k_SeeOther($this->url());
        } else {
            $values = $_POST;
        }
        return $this->render();
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

    function getYear()
    {
        $year = new Year($this->getKernel());
        $year->checkYear();
        return $year;
    }
}