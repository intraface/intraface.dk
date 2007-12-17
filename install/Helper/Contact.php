<?php
class Install_Helper_Contact {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function create() {
        
        require_once 'Intraface/modules/contact/Contact.php';
        $contact = new Contact($this->kernel);
        
        return $contact->save(array('name' => 'Contact 1'));
    }
}
?>
