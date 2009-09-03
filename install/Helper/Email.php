<?php
class Install_Helper_Email {

    private $kernel;
    private $db;

    public function __construct($kernel, $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    public function create()
    {
        require_once dirname(__FILE__) . '/Contact.php';
        $contact = new Install_Helper_Contact($this->kernel, $this->db);
        $contact_id = $contact->create();

        require_once 'Intraface/shared/email/Email.php';
        $email = new Email($this->kernel);

        $email->save(array('belong_to' => 1, 'type_id' => 2, 'contact_id' => $contact_id, 'subject' => 'test', 'body' => 'new test!'));
    }
}

