<?php
class Intraface_modules_debtor_Controller_Create extends k_Component
{
    protected $debtor;
    protected $template;
    protected $doctrine;

    function __construct(k_TemplateFactory $template, Doctrine_Connection_Common $doctrine)
    {
        $this->template = $template;
        $this->doctrine = $doctrine;
    }

    function map($name)
    {
        if ($name == 'contact') {
            return 'Intraface_modules_contact_Controller_Choosecontact';
        }

        return parent::map($name);
    }

    function renderHtml()
    {
        if ($this->query('contact_id') == '') {
            return new k_SeeOther($this->url('contact'));
        }
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
    	$debtor = $this->getDebtor();
    	$contact = new Contact($this->getKernel(), $this->body("contact_id"));

    	if ($this->body("contact_person_id") == "-1") {
    		$contact_person = new ContactPerson($contact);
    		$person["name"] = $_POST['contact_person_name'];
    		$person["email"] = $_POST['contact_person_email'];
    		$contact_person->save($person);
    		$contact_person->load();
    		$_POST["contact_person_id"] = $contact_person->get("id");
    	}

        if ($this->getKernel()->intranet->hasModuleAccess('currency') && $this->body('currency_id')) {
            $currency_module = $this->getKernel()->useModule('currency', false); // false = ignore user access
            $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
            $currency = $gateway->findById($this->body('currency_id'));
            if ($currency == false) {
                throw new Exception('Invalid currency');
            }

            $_POST['currency'] = $currency;
        }

    	if ($debtor->update($_POST)) {
    	    return new k_SeeOther($this->url('../' . $debtor->get('id')));
    	}

    	return $this->render();
    }

    function getValues()
    {
        if ($this->body()) {
            return $this->body();
        }

        $due_time = time() + $this->getContact()->get('paymentcondition') * 24 * 60 * 60;
        $due_date = date('d-m-Y', $due_time);

        return array(
            'number' => $this->getDebtor()->getMaxNumber() + 1,
            'dk_this_date' => date('d-m-Y'),
            'dk_due_date' => $due_date
        );
    }

    function getReturnUrl($contact_id)
    {
        return $this->url(null, array('create', 'contact_id' => $contact_id));
    }

    function getAction()
    {
        return 'Create';
    }

    function getType()
    {
        return $this->context->context->getType();
    }

    function getContact()
    {
        $module = $this->getKernel()->module('contact');
        return new Contact($this->getKernel(), $this->query('contact_id'));
    }


    function getDebtor()
    {
        if (is_object($this->debtor)) {
            return $this->debtor;
        }

        return $this->debtor = Debtor::factory($this->getKernel(), null, $this->getType());
    }

    function getPosts()
    {
        return $this->getDebtor()->getList();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

}