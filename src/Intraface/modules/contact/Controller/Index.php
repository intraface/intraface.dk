<?php
class Intraface_modules_contact_Controller_Index extends k_Component
{
    protected $eniro;

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        } elseif ($name == 'sendemail') {
            return 'Intraface_modules_contact_Controller_Sendemail';
        } elseif ($name == 'import') {
            return 'Intraface_modules_contact_Controller_Import';
        }
    }

    function getRedirect()
    {
        return Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getContact()
    {
        return $contact = new Contact($this->getKernel());
    }

    function getUsedKeywords()
    {
        $contact = new Contact($this->getKernel());
        $keywords = $contact->getKeywordAppender();
        $used_keywords = $keywords->getUsedKeywords();
        return $used_keywords;
    }

    function getContacts()
    {
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
        } else {
        	$contact->getDBQuery()->useCharacter();
        }

        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->usePaging('paging');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');

        return $contacts = $contact->getList();
    }

    function putForm()
    {
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
        		throw new Exception('Could not undelete');
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

        return new k_SeeOther($this->url());
    }

    function renderHtml()
    {
        if (!empty($_GET['search']) AND in_array($_GET['search'], array('hide', 'view'))) {
        	$this->getKernel()->setting->set('user', 'contact.search', $_GET['search']);
        }
        /*
        elseif (!empty($_GET['import'])) {
            $redirect = Intraface_Redirect::go($this->getKernel());
            $module_fileimport = $this->getKernel()->useModule('fileimport');
            $url = $redirect->setDestination($module_fileimport->getPath(), NET_SCHEME . NET_HOST . $this->url('import'));
            $redirect->askParameter('session_variable_name');
            return new k_SeeOther($url);
        }
        */

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

    function renderPdf()
    {
        $module = $this->getKernel()->module('contact');

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        $doc = new Intraface_modules_contact_PdfLabel($this->getKernel()->setting->get("user", "label"));
        $used_keyword = array();
        foreach ($contact->getDBQuery()->getKeyword() AS $kid) {
            foreach ($keywords AS $k){
                if ($k['id'] == $kid) {
                    $used_keyword[] = $k['keyword'];
                }
            }
        }
        $doc->generate($contacts, $contact->getDBQuery()->getFilter('search'), $used_keyword);
        $doc->stream();

    }

    function renderXls()
    {
        $module = $this->getKernel()->module('contact');
        $contact = new Contact($this->getKernel());

        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();

        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList('use_address');

        $keyword_ids = $contact->getDBQuery()->getKeyword();

        $used_keyword = array();

        if (is_array($keyword_ids) && count($keyword_ids) > 0) {

            foreach ($keyword_ids AS $kid) {
                foreach ($keywords AS $k){
                    if ($k['id'] == $kid) {
                        $used_keyword[] = $k['keyword'];
                    }
                }
            }

        }

        $keywords = 'N�gleord' . implode(' ', $used_keyword);
        $search = 'S�getekst' . $contact->getDBQuery()->getFilter('search');
        $count = 'Kontakter i s�gning' . count($contacts);

        $i = 1;

        // spreadsheet
        $workbook = new Spreadsheet_Excel_Writer();

        $workbook->send('kontakter.xls');

        $format_bold = $workbook->addFormat();
        $format_bold->setBold();
        $format_bold->setSize(8);

        $format_italic = $workbook->addFormat();
        $format_italic->setItalic();
        $format_italic->setSize(8);

        $format = $workbook->addFormat();
        $format->setSize(8);

        // Creating a worksheet
        $worksheet = $workbook->addWorksheet('Kontakter');


        $worksheet->write($i, 0, $this->getKernel()->intranet->get('name'), $format_bold);
        $i = $i + 1;
        $worksheet->write($i, 0, $search, $format_italic);
        $i = $i + 1;
        $worksheet->write($i, 0, $keywords, $format_italic);
        $i = $i + 1;
        $worksheet->write($i, 0, $count, $format_italic);

        $i = $i+2;
        $worksheet->write($i, 0, 'Navn', $format_bold);
        $worksheet->write($i, 1, 'Adresse', $format_bold);
        $worksheet->write($i, 2, 'Postnummer', $format_bold);
        $worksheet->write($i, 3, 'By', $format_bold);
        $worksheet->write($i, 4, 'Telefon', $format_bold);
        $worksheet->write($i, 5, 'Email', $format_bold);

        $i++;

        if (count($contacts) > 0) {
            foreach ($contacts AS $contact) {
                $worksheet->write($i, 0, $contact['name']);
                $worksheet->write($i, 1, $contact['address']['address']);
                $worksheet->write($i, 2, $contact['address']['postcode']);
                $worksheet->write($i, 3, $contact['address']['city']);
                $worksheet->write($i, 4, $contact['address']['phone']);
                $worksheet->write($i, 5, $contact['address']['email']);
                $i++;
            }

        }
        $worksheet->hideGridLines();

        // Let's send the file


        $workbook->close();

        exit;
    }

    function renderHtmlCreate()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/edit.tpl.php');
        return $smarty->render($this);

    }

    function getContactModule()
    {
        return $contact_module = $this->getKernel()->module("contact");

    }

    function postForm()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if (!empty($_POST['eniro']) AND !empty($_POST['eniro_phone'])) {
            $contact = new Contact($this->getKernel(), $_POST['id']);

            $eniro = new Services_Eniro();
            $value = $_POST;

            if ($oplysninger = $eniro->query('telefon', $_POST['eniro_phone'])) {

                // skal kun bruges s� l�nge vi ikke er utf8
                // $oplysninger = array_map('utf8_decode', $oplysninger);
                $address['name'] = $oplysninger['navn'];
                $address['address'] = $oplysninger['adresse'];
                $address['postcode'] = $oplysninger['postnr'];
                $address['city'] = $oplysninger['postby'];
                $address['phone'] = $_POST['eniro_phone'];
            }
            $this->eniro = $address;
            $this->render();
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // for a new contact we want to check if similar contacts alreade exists
            if (empty($_POST['id'])) {
                $contact = new Contact($this->getKernel());
                if (!empty($_POST['phone'])) {
                    $contact->getDBQuery()->setCondition("address.phone = '".$_POST['phone']."' AND address.phone <> ''");
                    $similar_contacts = $contact->getList();
                }

            } else {
                $contact = new Contact($this->getKernel(), $_POST['id']);
            }

            // checking if similiar contacts exists
            if (!empty($similar_contacts) AND count($similar_contacts) > 0 AND empty($_POST['force_save'])) {
            } elseif ($id = $contact->save($_POST)) {

                // $redirect->addQueryString('contact_id='.$id);
                if ($redirect->get('id') != 0) {
                    $redirect->setParameter('contact_id', $id);
                }
                return new k_SeeOther($redirect->getRedirect($this->url($id)));

                //$contact->lock->unlock_post($id);
            }

            $value = $_POST;
            $address = $_POST;
            $delivery_address = array();
            $delivery_address['name'] = $_POST['delivery_name'];
            $delivery_address['address'] = $_POST['delivery_address'];
            $delivery_address['postcode'] = $_POST['delivery_postcode'];
            $delivery_address['city'] = $_POST['delivery_city'];
            $delivery_address['country'] = $_POST['delivery_country'];
        }

        return $this->render();

    }

    function getValues()
    {
        if (!empty($this->eniro)) {
            return $this->eniro;
        }
        return array('number' => $this->getContact()->getMaxNumber()+1);
    }

    function getAddressValues()
    {
        return array();
    }

    function getDeliveryAddressValues()
    {
        return array();
    }


}