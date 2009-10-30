<?php
class Intraface_modules_contact_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getContact()
    {
return $contact = new Contact($this->getKernel());
    }

    function getUsedKeywords()
    {
        // hente liste med kunder
$contact = new Contact($this->getKernel());
$keywords = $contact->getKeywordAppender();
$used_keywords = $keywords->getUsedKeywords();
        return $used_keywords;
    }

    function getContacts()
    {

// hente liste med kunder
$contact = new Contact($this->getKernel());
$keywords = $contact->getKeywordAppender();
$used_keywords = $keywords->getUsedKeywords();

if (isset($_GET['query']) || isset($_GET['keyword_id'])) {

	if (isset($_GET['query'])) {
		$contact->getDBQuery()->setFilter('search', $_GET['query']);
	}

	if (isset($_GET['keyword_id'])) {
		$contact->getDBQuery()->setKeyword($_GET['keyword_id']);
	}
}
else {
	$contact->getDBQuery()->useCharacter();
}

$contact->getDBQuery()->defineCharacter('character', 'address.name');
$contact->getDBQuery()->usePaging('paging');
$contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');

return $contacts = $contact->getList();
    }

    function renderHtml()
    {
        $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');

$module = $this->getKernel()->module('contact');
$translation = $this->getKernel()->getTranslation('contact');

// settings
if (!empty($_GET['search']) AND in_array($_GET['search'], array('hide', 'view'))) {
	$this->getKernel()->setting->set('user', 'contact.search', $_GET['search']);
}

// delete
if (!empty($_POST['action']) AND $_POST['action'] == 'delete') {
	$deleted = array();
	if (!empty($_POST['selected']) AND is_array($_POST['selected'])) {
		foreach ($_POST['selected'] AS $key=>$id) {
			$contact = new Contact($this->getKernel(), intval($id));
			if ($contact->delete()) {
				$deleted[] = $id;
			}
		}
	}
} elseif (!empty($_POST['undelete'])) {

	if (!empty($_POST['deleted']) AND is_string($_POST['deleted'])) {
		$undelete = unserialize(base64_decode($_POST['deleted']));
	} else {
		trigger_error('could not undelete', E_USER_ERROR);
	}
	if (!empty($undelete) AND is_array($undelete)) {
		foreach ($undelete AS $key=>$id) {
			$contact = new Contact($this->getKernel(), intval($id));
			if (!$contact->undelete()) {
			// void
			}
		}
	}
}

if (!empty($_GET['import'])) {
    $redirect = Intraface_Redirect::go($this->getKernel());
    $shared_fileimport = $this->getKernel()->useShared('fileimport');
    $url = $redirect->setDestination($shared_fileimport->getPath().'index.php', $module->getPath().'import.php');
    $redirect->askParameter('session_variable_name');
    header('location: '.$url);
    exit;

}

/*
if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$contact = new Contact($this->getKernel(), $_GET['delete']);
	$delete = $contact->delete();
}
elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
	$contact = new Contact($this->getKernel(), $_GET['undelete']);
	$contact->undelete();
}
*/




        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLists()
    {
        $list = new NewsletterList($this->getKernel());
        return $list->getList();
    }

    function t($phrase)
    {
         return $phrase;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        }

    }
}