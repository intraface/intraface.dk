<?php
class Intraface_modules_accounting_Controller_State extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Accounting state');    
    
        if (in_array($this->query('message'), array('hide'))) {
            $this->getKernel()->setting->set('user', 'accounting.state.message', 'hide');
        } elseif (in_array($this->query('message2'), array('hide'))) {
            $this->getKernel()->setting->set('user', 'accounting.state.message2', 'hide');
        }

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/state');
        return $tpl->render($this);
    }

    function postForm()
    {
        $voucher = new Voucher($this->getYear());

        if (!$voucher->stateDraft()) {
            $post->error->set('Posterne kunne ikke bogføres');
        }

        return new k_SeeOther($this->url());
    }

    function getPosts()
    {
        return $this->getPost()->getList('draft');
    }

    function getAccounts()
    {
        return $this->getYear()->getBalanceAccounts();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        $year = $this->context->getYear();
        $year->checkYear();
        return $year;
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

    function getVoucher()
    {
        require_once dirname(__FILE__) . '/../Voucher.php';
        return new Voucher($this->getYear());
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getPost()
    {
    	return new Post($this->getVoucher());
    }
}
