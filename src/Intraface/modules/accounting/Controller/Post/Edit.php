<?php
class Intraface_modules_accounting_Controller_Post_Edit extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function GET()
    {
        if (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
            $post = Post::factory($year, (int)$_GET['id']);
            $values = $post->get();
            $values['date'] = $post->get('date_dk');
            $values['debet'] = $post->get('debet');
            $values['credit'] = $post->get('credit');
        } elseif (!empty($_GET['voucher_id']) AND is_numeric($_GET['voucher_id'])) {
            $post = new Post(new Voucher($year, $_GET['voucher_id']));
            $values['date'] = $post->voucher->get('date_dk');
        } else {
            // setting variables
            $post = Post::factory($year);
            $values['date'] = date('d-m-Y');
            $values['debet_account_number'] = '';
            $values['credit_account_number'] = '';
            $values['amount'] = '';
            $values['text'] = '';
            $values['id'] = '';
        }
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/post/edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $year = $this->getYear();
        $year->checkYear();

        // tjek om debet og credit account findes
        $post = new Post(new Voucher($year, $_POST['voucher_id']), $_POST['id']);
        $account = Account::factory($year, $_POST['account']);

        $date = new Intraface_Date($_POST['date']);
        $date->convert2db();

        $debet = new Intraface_Amount($_POST['debet']);
        if (!$debet->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $debet = $debet->get();

        $credit = new Intraface_Amount($_POST['credit']);
        if (!$credit->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $credit = $credit->get();

        if ($id = $post->save($date->get(), $account->get('id'), $_POST['text'], $debet, $credit)) {
            header('Location: voucher.php?id='.$post->voucher->get('id').'&from_post_id='.$id);
            exit;
        } else {
            $values = $_POST;
        }

    }

    function getKernel()
    {
        $registry = $this->registry->create();
        return $registry->get('kernel');
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        if (!is_numeric($this->name())) {
        	return new Year($this->getKernel());
        } else {
        	return new Year ($this->getKernel(), $this->name());
        }
    }

    function getAccount()
    {
        return new Account($year);
    }

    function getYearGateway()
    {
        return $this->context->getYearGateway();
    }
}