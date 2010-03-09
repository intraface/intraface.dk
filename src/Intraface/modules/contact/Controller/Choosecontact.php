<?php
class Intraface_modules_contact_Controller_Choosecontact extends k_Component
{
    protected $contact;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        /*
        $redirect = $this->getRedirect();

        if (!empty($_GET['add'])) {
        	$add_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
        	$url = $add_redirect->setDestination($module->getPath()."contact_edit.php", NET_SCHEME . NET_HOST . $this->url(null, array($redirect->get('redirect_query_string'))));
        	$add_redirect->askParameter("contact_id");
        	//$add_redirect->setParameter("selected_contact_id", intval($_GET['add']));
        	return new k_SeeOther($url);
        } elseif (!empty($_GET['return_redirect_id'])) {
            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($return_redirect->getParameter('contact_id') != 0) {
                $redirect->setParameter('contact_id', $return_redirect->getParameter('contact_id'));
                return new k_SeeOther($redirect->getRedirect($this->url('../')));
            }
        }
        */

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/choosecontact');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getContact()
    {
        if (is_object($this->contact)) {
            return $this->contact;
        }
        return $this->contact = new Contact($this->getKernel());
    }

    function getContacts()
    {
        $contact = $this->getContact();
        if (!empty($_GET['contact_id'])) {
        	$contact->getDBQuery()->setCondition("contact.id = ".intval($_GET['contact_id']));
        } elseif (isset($_GET['query']) || isset($_GET['keyword_id'])) {

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
        $contact->getDBQuery()->storeResult('use_stored', 'select_contact', 'sublevel');

        if (isset($_GET['contact_id']) && intval($_GET['contact_id']) != 0) {
        	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['contact_id']));
        } elseif (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0) {
        	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['last_contact_id']));
        }

        return $contacts = $contact->getList();
    }

    function getRedirect()
    {
        return $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getUsedKeywords()
    {
        $keywords = $this->getContact()->getKeywordAppender();
        return $used_keywords = $keywords->getUsedKeywords();
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        }
    }

    function getRedirectUrl($contact_id = 0)
    {
        return $this->context->getReturnUrl($contact_id);
    }

    function postForm()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if (!empty($_POST['eniro']) AND !empty($_POST['eniro_phone'])) {
            $contact = $this->getContact();

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
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // for a new contact we want to check if similar contacts alreade exists
            if (empty($_POST['id'])) {
                $contact = $this->getContact();
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
                return new k_SeeOther($this->url('../', array('contact_id' => $id)));

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

    function getContactModule()
    {
        return $this->getKernel()->module("contact");
    }

    function getValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return array('number' => $this->getContact()->getMaxNumber() + 1);
    }

    function getAddressValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return array();
    }

    function getDeliveryAddressValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return array();
    }

    function renderHtmlCreate()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $smarty->render($this);

    }

    function putForm()
    {
        $module = $this->getKernel()->module('contact');

        $contact = new Contact($this->getKernel(), intval($_POST['selected']));
    	if ($contact->get('id') != 0) {
    	    return new k_SeeOther($this->getRedirectUrl($contact->get('id')));
    	} else {
    		$contact->error->set("Du skal vælge en kontakt");
    	}

    	return $this->render();
    }
}
