<?php
class Intraface_modules_accounting_Controller_Daybook extends k_Component
{
    protected $post;
    protected $voucher;
    protected $year;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'state') {
            return 'Intraface_modules_accounting_Controller_State';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->useModule('accounting');

        $this->document->addScript('focusField.js');
        $this->document->addScript('accounting/daybook.js');

        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
            $this->getKernel()->getSetting()->set('user', 'accounting.daybook.message', 'hide');
        } elseif (!empty($_GET['quickhelp']) AND in_array($_GET['quickhelp'], array('true', 'false'))) {
            $this->getKernel()->getSetting()->set('user', 'accounting.daybook_cheatsheet', $_GET['quickhelp']);
            if (isAjax()) {
                echo '1';
                exit;
            }
        } elseif (!empty($_GET['view']) AND in_array($_GET['view'], array('income', 'expenses', 'classic', 'debtor'))) {
            $this->getKernel()->getSetting->set('user', 'accounting.daybook_view', $_GET['view']);
        }

        // tests for the setup
        if (!$this->getAccount()->anyAccounts()) {
            $tpl = $this->template->create('Intraface/Controller/templates/message');
            return $tpl->render($this, array(
            	'type' => 'dependent',
            	'content' => 'Du skal først oprette nogle konti, inden du kan taste poster ind i regnskabet. Du kan oprette en standardkontoplan under <a href="' .  $this->url('../year/' . $this->getYear()->get('id')) . '">regnskabsåret</a>.'));
        } elseif ($this->getYear()->get('vat') == 1 AND !$this->getYear()->vatAccountIsSet()) {
            $tpl = $this->template->create('Intraface/Controller/templates/message');
            return $tpl->render($this, array(
            	'type' => 'dependent',
            	'content' => 'Du har ikke sat momskonti. <a href="' . $this->url('../settings') . '">Gå til indstillingerne</a>.'));
        }

        // the view to use
        $available_views = array('expenses', 'income', 'debtor');

        if (in_array($this->query('view'), $available_views)) {
            $view_tpl = $this->template->create(dirname(__FILE__) . '/templates/daybook/' . $this->query('view'));
        } else {
            $view_tpl = $this->template->create(dirname(__FILE__) . '/templates/daybook/default');
        }

        // posts in draft
        if (count($this->getPostsInDraft()) > 0) {
            $draft_tpl = $this->template->create(dirname(__FILE__) . '/templates/daybook/posts');
            $draft = $draft_tpl->render($this);
        } else {
            $draft = '<p>Der er ikke nogen poster i kassekladden.</p>';
        }

        // initial message
        if ($this->getKernel()->getSetting()->get('user', 'accounting.daybook.message') == 'view') {
            $msg_tpl = $this->template->create(dirname(__FILE__) . '/templates/daybook/message');
            $message = $msg_tpl->render($this);
        } else {
            $message = '';
        }

        // cheatsheet
        if ($this->getKernel()->setting->get('user', 'accounting.daybook_cheatsheet')== 'true') {
            $cheat_tpl = $this->template->create(dirname(__FILE__) . '/templates/daybook/cheatsheet');
            $cheatsheet = $cheat_tpl->render($this);
        } else {
            $cheatsheet = '<ul class="options">
    			<li><a href="' . $this->url(null, array('quickhelp' => 'true')) . '">Slå hurtighjælp til</a></li>
    			</ul>';
        }

        // outputting the entire page
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/daybook');
        return $tpl->render($this, array(
        	'cheatsheet' => $cheatsheet,
        	'message' => $message,
        	'draft' => $draft,
        	'view' => $view_tpl->render($this)));
    }

    function postForm()
    {
        // tjek om debet og credit account findes
        //$voucher = Voucher::factory($this->getYear(), $_POST['voucher_number']);
        $voucher = $this->getVoucher($_POST['voucher_number']);
        if ($id = $voucher->saveInDaybook($_POST)) {
            return new k_SeeOther($this->url(null, array('flare' => 'Post has been added', 'view' => $this->query('view'))));
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        if (is_object($this->year)) {
            return $this->year;
        }
        require_once 'Intraface/modules/accounting/Year.php';

        $year = new Year($this->getKernel());
        $year->checkYear();
        return $this->year = $year;
    }

    function getYearGateway()
    {
        return new Intraface_modules_accounting_YearGateway($this->getKernel());
    }

    function getVoucher($voucher_number = null)
    {
        $this->getKernel()->useModule('accounting');
        if (is_object($this->voucher)) {
    	    return $this->voucher;
    	}
        return ($this->voucher = Voucher::factory($this->getYear(), $voucher_number));
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
        require_once 'Intraface/modules/accounting/Account.php';
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
}