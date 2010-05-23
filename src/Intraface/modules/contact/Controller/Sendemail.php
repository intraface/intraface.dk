<?php
class Intraface_modules_contact_Controller_Sendemail extends k_Component
{
    protected $msg;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getMessage()
    {
        return $this->msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
    	$validator = new Intraface_Validator($this->getContact()->error);
    	$validator->isString($_POST['subject'], 'error in subject');
    	$validator->isString($_POST['text'], 'error in text');

    	$contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        if (!$contact->error->isError()) {
    		// valideret subject og body
    		$j = 0;

    		for ($i = 0, $max = count($contacts); $i < $max; $i++) {
    			if (!$validator->isEmail($contacts[$i]['email'], "")) {
    				// Hvis de ikke har en mail, k�rer vi videre med n�ste.
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
    				'belong_to' => 0 // der er ikke nogen specifik id at s�tte
    			);

    			$email->save($input);
    			$email->queue();
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
        $this->getKernel()->useShared('email');

        $_GET['use_stored'] = true;

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        $data = array(
            'contacts' => $contacts,
            'contact' => $contact
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/sendemail');
        return $smarty->render($this, $data);
    }

    function getContact()
    {
        $this->getKernel()->useShared('email');

        $_GET['use_stored'] = true;

        return $contact = new Contact($this->getKernel());
    }

    function getContacts()
    {
        $this->getKernel()->useShared('email');

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
