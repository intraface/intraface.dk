<?php
class Intraface_modules_contact_Controller_Index extends k_Component
{
    protected $eniro;
    protected $contact;
    protected $template;
    protected $db_sql;
    protected $contact_gateway;

    function __construct(k_TemplateFactory $template, DB_Sql $db)
    {
        $this->template = $template;
        $this->db_sql = $db;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        } elseif ($name == 'sendemail') {
            return 'Intraface_modules_contact_Controller_Sendemail';
        } elseif ($name == 'import') {
            return 'Intraface_modules_contact_Controller_Import';
        } elseif ($name == 'batchnewsletter') {
            return 'Intraface_modules_contact_Controller_BatchNewsletter';
        } elseif ($name == 'memos') {
            return 'Intraface_modules_contact_Controller_Memos';
        }
    }

    function renderHtml()
    {
        if (in_array($this->query('search'), array('hide', 'view'))) {
            $this->getKernel()->setting->set('user', 'contact.search', $this->query('search'));
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

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this, array('contacts' => $this->getContacts()));
    }

    function putForm()
    {
        if ($this->body('action') == 'delete') {
            $deleted = array();
            if (is_array($this->body('selected'))) {
                foreach ($this->body('selected') as $key => $id) {
                    $contact = $this->getGateway()->findById(intval($id));
                    if ($contact->delete()) {
                        $deleted[] = $id;
                    }
                }
            }
        } elseif ($this->body('undelete')) {
            if (is_string($this->body('deleted'))) {
                $undelete = unserialize(base64_decode($this->body('deleted')));
            } else {
                throw new Exception('Could not undelete');
            }
            if (!empty($undelete) and is_array($undelete)) {
                foreach ($undelete as $key => $id) {
                    $contact = $this->getGateway()->findById(intval($id));
                    if (!$contact->undelete()) {
                    // void
                    }
                }
            }
        }

        return new k_SeeOther($this->url());
    }

    function renderPdf()
    {
        $module = $this->getKernel()->module('contact');

        $contact = $this->getContact();
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        $doc = new Intraface_modules_contact_PdfLabel($this->getKernel()->setting->get("user", "label"));
        $used_keyword = array();
        foreach ($contact->getDBQuery()->getKeyword() as $kid) {
            foreach ($keywords as $k) {
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
        $contact = $this->getContact();

        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();

        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList('use_address');

        $keyword_ids = $contact->getDBQuery()->getKeyword();

        $used_keyword = array();

        if (is_array($keyword_ids) && count($keyword_ids) > 0) {
            foreach ($keyword_ids as $kid) {
                foreach ($keywords as $k) {
                    if ($k['id'] == $kid) {
                        $used_keyword[] = $k['keyword'];
                    }
                }
            }
        }

        $keywords = 'Nøgleord' . implode(' ', $used_keyword);
        $search = 'Søgetekst' . $contact->getDBQuery()->getFilter('search');
        $count = 'Kontakter i søgning' . count($contacts);

        $i = 1;

        // spreadsheet
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);

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
        $worksheet->setInputEncoding('UTF-8');

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
            foreach ($contacts as $contact) {
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
    }

    function renderHtmlCreate()
    {
        $contact_module = $this->getKernel()->module("contact");
        $contact_module->includeFile('ContactReminder.php');

        $this->document->addScript('contact/contact_edit.js');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $contact_module = $this->getKernel()->module("contact");
        $contact_module->includeFile('ContactReminder.php');

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if ($this->body('eniro_phone')) {
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
        } else {
            // for a new contact we want to check if similar contacts alreade exists
            if (!empty($_POST['phone'])) {
                $this->getContact()->getDBQuery()->setCondition("address.phone = '".$_POST['phone']."' AND address.phone <> ''");
                $similar_contacts = $this->getContact()->getList();
            }

            // checking if similiar contacts exists
            if (!empty($similar_contacts) and count($similar_contacts) > 0 and empty($_POST['force_save'])) {
            } elseif ($id = $this->getContact()->save($_POST)) {
                // $redirect->addQueryString('contact_id='.$id);
                if ($redirect->get('id') != 0) {
                    $redirect->setParameter('contact_id', $id);
                }
                return new k_SeeOther($redirect->getRedirect($this->url($id)));

                //$contact->lock->unlock_post($id);
            }
        }

        return $this->render();
    }

    function getContactModule()
    {
        return $contact_module = $this->getKernel()->module("contact");
    }

    function getValues()
    {
        if (!empty($this->eniro)) {
            return $this->eniro;
        }
        if ($this->body()) {
            return $value = $_POST;
        }
        return array('number' => $this->getContact()->getMaxNumber()+1);
    }

    function getAddressValues()
    {
        if ($this->body()) {
            return $address = $_POST;
        }
        return array();
    }

    function getDeliveryAddressValues()
    {
        if ($this->body()) {
            $delivery_address = array();
            $delivery_address['name'] = $_POST['delivery_name'];
            $delivery_address['address'] = $_POST['delivery_address'];
            $delivery_address['postcode'] = $_POST['delivery_postcode'];
            $delivery_address['city'] = $_POST['delivery_city'];
            $delivery_address['country'] = $_POST['delivery_country'];
            return $delivery_address;
        }

        return array();
    }

    function getGateway()
    {
        if (is_object($this->contact_gateway)) {
            return $this->contact_gateway;
        }

        return $this->contact_gateway = new Intraface_modules_contact_ContactGateway($this->getKernel(), $this->db_sql);
    }

    function getRedirect()
    {
        return Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getContact()
    {
        if (is_object($this->contact)) {
            return $this->contact;
        }
        return $this->contact = new Contact($this->getKernel());
    }

    function getUsedKeywords()
    {
        $contact = $this->getContact();
        $keywords = $contact->getKeywordAppender();
        $used_keywords = $keywords->getUsedKeywords();
        return $used_keywords;
    }

    function getContacts()
    {
        $keywords = $this->getContact()->getKeywordAppender();
        $used_keywords = $keywords->getUsedKeywords();

        if (isset($_GET['query']) || isset($_GET['keyword_id'])) {
            if (isset($_GET['query'])) {
                $this->getContact()->getDBQuery()->setFilter('search', $_GET['query']);
            }

            if (isset($_GET['keyword_id'])) {
                $this->getContact()->getDBQuery()->setKeyword($_GET['keyword_id']);
            }
        } else {
            $this->getContact()->getDBQuery()->useCharacter();
        }

        $this->getContact()->getDBQuery()->defineCharacter('character', 'address.name');
        $this->getContact()->getDBQuery()->usePaging('paging');
        $this->getContact()->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $this->getContact()->getDBQuery()->setUri($this->url());

        return ($contacts = $this->getContact()->getList());
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}
