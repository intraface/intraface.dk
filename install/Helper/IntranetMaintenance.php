<?php

class Install_Helper_IntranetMaintenance {
    
    private $kernel;
    private $db;
    
    public function __construct($kernel, $db) 
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    
    
    public function createAlternativeIntranet() 
    {
        
        require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
        $intranet = new IntranetMaintenance();
        $intranet->save(array('name' => 'Test', 'identifier' => 'test'), 1);
        $intranet->setModuleAccess('administration', 2);
        $intranet->setModuleAccess('modulepackage', 2);
        
        require_once 'Intraface/modules/intranetmaintenance/UserMaintenance.php';
        $user = new UserMaintenance(1);
        $user->setIntranetAccess(2);
        $user->setModuleAccess('administration', 2);
        $user->setModuleAccess('modulePackage', 2);
        
    }
}

?>
