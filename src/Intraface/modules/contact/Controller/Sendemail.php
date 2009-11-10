<?php
class Intraface_modules_contact_Controller_Sendemail extends k_Component
{
    protected $registry;
    protected $msg;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getMessage()
    {
        return $this->msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function postForm()
    {
    	$validator = new Intraface_Validator($this->getContact()->error);
    	$validator->isString($_POST['subject'], 'error in subject');
    	$validator->isString($_POST['text'], 'error in text');

    	if (!$this->getContact()->error->isError()) {
    		// valideret subject og body
    		$j = 0;

    		for ($i = 0, $max = count($contacts); $i < $max; $i++) {
    			if (!$validator->isEmail($contacts[$i]['email'], "")) {
    				// Hvis de ikke har en mail, kører vi videre med næste.
    				continue;
    			}

    			$contact = new Contact($this->getKernel(), $contacts[$i]['id']);

    			$email = new Email($this->getKernel());
    			$input = array(
    				'subject' => $_POST['subject'],
    				'body' => $_POST['text'] . "\n\nLogin: " . $contact->get('login_url'),
    				'from_email' => $this->getKernel()->user->get('email'),
    				'from_name' => $this->getKernel()->user->get('name'),
    				'contact_id' => $contact->get('id'),
    				'type_id' => 11, // email til search
    				'belong_to' => 0 // der er ikke nogen specifik id at sætte
    			);

    			$email->save($input);
    			// E-mailen sættes i kø - hvis vi sender den med det samme tager det
    			// alt for lang tid.
    			$email->send(Intraface_Mail::factory(), 'queue');
    			$j++;
    		}
    		$this->msg = 'Emailen blev i alt sendt til ' . $j . ' kontakter. <a href="'.$this->url('../').'">Tilbage til kontakter</a>.';
    	} else {
    		$value = $_POST;
    	}

    	return $this->render();
    }

    function renderHtml()
    {
        $this->getKernel()->module('contact');
        $this->getKernel()->useShared('email');
        $translation = $this->getKernel()->getTranslation('contact');

        $_GET['use_stored'] = true;

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        $smarty = new k_Template(dirname(__FILE__) . '/templates/sendemail.tpl.php');
        return $smarty->render($this);
    }

    function getContact()
    {
        $this->getKernel()->module('contact');
        $this->getKernel()->useShared('email');
        $translation = $this->getKernel()->getTranslation('contact');

        $_GET['use_stored'] = true;

        return $contact = new Contact($this->getKernel());
    }

    function getContacts()
    {
        $this->getKernel()->module('contact');
        $this->getKernel()->useShared('email');
        $translation = $this->getKernel()->getTranslation('contact');

        $_GET['use_stored'] = true;

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");
        return $contact;
    }
}
