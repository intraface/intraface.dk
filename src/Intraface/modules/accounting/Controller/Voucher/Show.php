<?php
class Intraface_modules_accounting_Controller_Voucher_Show extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        /*
$module_accounting = $kernel->module('accounting');
$kernel->useShared('filehandler');
$translation = $kernel->getTranslation('accounting');


$not_all_stated  = false;

$year = new Year($kernel);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	if (!empty($_GET['return_redirect_id']) AND is_numeric($_GET['return_redirect_id'])) {

		$redirect = Intraface_Redirect::factory($kernel, 'return');
		$selected_file_id = $redirect->getParameter('file_handler_id');

		if ($selected_file_id != 0) {
			$voucher = new Voucher($year, intval($_GET['id']));
			$voucher_file = new VoucherFile($voucher);
			$var['belong_to'] = 'file';
			$var['belong_to_id'] = intval($selected_file_id);
			$voucher_file->save($var);
		}
	}
}

if (!empty($_GET['delete']) AND is_numeric($_GET['delete']) AND !empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$voucher = new Voucher($year, $_GET['id']);
	$post = new Post($voucher, $_GET['delete']);
	if ($post->delete()) {
		header('Location: voucher.php?id='.$voucher->get('id'));
		exit;
	}
}

if (!empty($_GET['delete_file']) AND is_numeric($_GET['delete_file'])) {

	$voucher = new Voucher($year, $_GET['id']);
	$voucher_file = new VoucherFile($voucher, $_GET['delete_file']);
	if ($voucher_file->delete()) {
		header('Location: voucher.php?id='.$voucher->get('id'));
		exit;
	} else {
		trigger_error('Kunne ikke slette filen');
	}
} elseif (!empty($_POST) AND !empty($_POST['state'])) {
	$voucher = new Voucher($year, $_POST['id']);
	$voucher->stateVoucher();
} elseif (!empty($_POST) AND !empty($_FILES)) {
	$voucher = new Voucher($year, $_POST['id']);
	$voucher_file = new VoucherFile($voucher);
	$var['belong_to'] = 'file';

	if (!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
		$redirect = Intraface_Redirect::factory($kernel, 'go');
		$module_filemanager = $kernel->useModule('filemanager');
		$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_accounting->getPath().'voucher.php?id='.$voucher->get('id'));
		// $redirect->setIdentifier('voucher'); // Den er der kun behov for, hvis der er flere redirect med return p� samme side  /Sune 06-12-2006
		$redirect->askParameter('file_handler_id');
		header('Location: '.$url);
		exit;
	} elseif (!empty($_FILES['new_file'])) {
		$filehandler = new FileHandler($kernel);
		$filehandler->createUpload();
		$filehandler->upload->setSetting('max_file_size', 2000000);
		if ($id = $filehandler->upload->upload('new_file')) {
			$var['belong_to_id'] = $id;
			if (!$voucher_file->save($var)) {
				$value = $_POST;
			} else {
				header('Location: voucher.php?id='.$voucher->get('id'));
				exit;
			}

		} else {
			$filehandler->error->view();
			$voucher_file->error->set('Kunne ikke uploade filen');
			$voucher_file->error->view();
		}

	}
} else {
	$voucher = new Voucher($year, $_GET['id']);
}

$posts = $voucher->getPosts();
$voucher_file = new VoucherFile($voucher);
$voucher_files = $voucher_file->getList();
*/
        require_once dirname(__FILE__) . '/../../Voucher.php';

        $voucher = new Voucher($this->getYear(), $this->name());

        $posts = $voucher->getPosts();
        $voucher_file = new VoucherFile($voucher);
        $voucher_files = $voucher_file->getList();

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/voucher/show.tpl.php');
        return $smarty->render($this);
    }

    function getFiles()
    {
        $voucher_file = new VoucherFile($this->getVoucher());
        return $voucher_files = $voucher_file->getList();
    }

    function getVoucherFile()
    {
        return $voucher_file = new VoucherFile($this->getVoucher());
    }

    function getVoucher()
    {
        return $voucher = new Voucher($this->getYear(), $this->name());
    }

    function getPosts()
    {
        return $posts = $this->getVoucher()->getPosts();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getFilehandlerHtml()
    {
        require_once 'Intraface/shared/filehandler/FileHandler.php';
        require_once 'Intraface/shared/filehandler/FileHandlerHTML.php';
        $filehandler = new FileHandler($this->getKernel());
        return $filehandler_html = new FileHandlerHTML($filehandler);
    }

    function getYear()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        $year = new Year($this->getKernel());
        $year->checkYear();
        return $year;
    }

    function POST()
    {
/*
    } elseif(!empty($_POST) AND !empty($_POST['action']) && $_POST['action'] == 'counter_entry' ) {

	$voucher = new Voucher($year, $_POST['id']);
	$posts = $voucher->getPosts();

	foreach($posts as $post) {
		if(is_array($_POST['selected']) && in_array($post['id'], $_POST['selected'])) {
			$new_post = new Post($voucher);
			$new_post->save($post['date'], $post['account_id'], $post['text'].' - '.t('counter entry'), $post['credit'], $post['debet']);
		}
	}
  */
        return parent::POST();
    }

    function getValues()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        return $this->getVoucher()->get();
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

    function renderHtmlEdit()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/voucher/edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $module = $this->getKernel()->module('accounting');
        $translation = $this->getKernel()->getTranslation('accounting');

        $voucher = new Voucher($this->getYear(), $this->name());
    	if ($voucher->save($_POST)) {
    	    return new k_SeeOther($this->url(null));
    	}
    	return $this->render();
    }
}