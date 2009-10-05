<?php
class Intraface_modules_accounting_Controller_Daybook extends k_Component
{
    protected $registry;
    protected $post;
    protected $voucher;

    protected function map($name)
    {
        if ($name == 'state') {
            return 'Intraface_modules_accounting_Controller_State';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
            $this->getKernel()->setting->set('user', 'accounting.daybook.message', 'hide');
        } elseif (!empty($_GET['quickhelp']) AND in_array($_GET['quickhelp'], array('true', 'false'))) {
            $this->getKernel()->setting->set('user', 'accounting.daybook_cheatsheet', $_GET['quickhelp']);
            if (isAjax()) {
                echo '1';
                exit;
            }
        }
        /*
        elseif (!empty($_GET['view']) AND in_array($_GET['view'], array('income', 'expenses', 'classic', 'debtor'))) {
            $this->getKernel()->setting->set('user', 'accounting.daybook_view', $_GET['view']);
        }
        */

         $tpl = new k_Template(dirname(__FILE__) . '/templates/daybook.tpl.php');
        return $tpl->render($this);
    }

    function getKernel()
    {
        $registry = $this->registry->create();
        return $registry->get('kernel');
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

    function postForm()
    {
        //$this->getVoucher();
        // tjek om debet og credit account findes
        //$voucher = Voucher::factory($this->getYear(), $_POST['voucher_number']);
        $voucher = $this->getVoucher($_POST['voucher_number']);
        if ($id = $voucher->saveInDaybook($_POST)) {
            return new k_SeeOther($this->url(null, array('flare' => 'Post has been added', 'view' => $this->query('view'))));
        }
        return $this->render();
    }

    function getVoucher($voucher_number = null)
    {
        require_once dirname(__FILE__) . '/../Voucher.php';

        if (is_object($this->voucher)) {
    	    return $this->voucher;
    	}
        return ($this->voucher = new Voucher($this->getYear(), $voucher_number));
    }

    function getValues()
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        $values['voucher_number'] = $this->getVoucher()->getMaxNumber() + 1;
        $values['date'] = date('d-m-Y');
        $values['debet_account_number'] = '';
        $values['credit_account_number'] = '';
        $values['amount'] = '';
        $values['text'] = '';
        $values['reference'] = '';
        $values['id'] = '';

    	return $values;
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getPost()
    {
    	if (is_object($this->post)) {
    	    return $this->post;
    	}
        return ($this->post = new Post($this->getVoucher()));
    }

    function getPostsInDraft()
    {
        return $this->getPost()->getList('draft');
    }

    function t($phrase)
    {
        return $phrase;
    }
}