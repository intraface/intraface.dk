<?php

class Install_Helper_Administration
{
    private $kernel;
    private $db;
    
    public function __construct($kernel, $db) 
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function FillInIntranetAddress() {
        
        $this->kernel->intranet->address->save(array('name' => 'Intraface', 
            'address' => "Gade 1\nNørre Snede", 
            'postcode' => 1000, 
            'city' => 'Storre by', 
            'email' => 'start@intraface.dk'));
        
    }
    
}

?>
