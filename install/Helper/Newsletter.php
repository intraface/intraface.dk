<?php
class Install_Helper_Newsletter {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function create() {
        
        require_once 'Intraface/modules/newsletter/NewsletterList.php';
        $newsletter = new NewsletterList($this->kernel);
        
        return $newsletter->save(array('title' => 'Test'));
    }
}
?>
