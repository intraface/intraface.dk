<?php

class Install_Helper_IntranetMaintenance {
    
    private $kernel;
    private $db;
    
    public function __construct($kernel, $db) 
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function loadPackages() 
    {
        
        $sql_structure = file_get_contents(dirname(__FILE__) . '/../database-module_package-values.sql');
        $sql_arr = Intraface_Install::splitSql($sql_structure);

        foreach($sql_arr as $sql) {
            if(empty($sql)) { continue; }
            $result = $this->db->exec($sql);
            if(PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
                exit;
            }
        }
        
        require_once 'Intraface/modules/product/Product.php';
        $product = new Product($this->kernel);
        
        $product->save(array('name' => 'Small CMS', 'unit' => 4, 'vat' => 1, 'price' => 10));
        $product->save(array('name' => 'Medium CMS', 'unit' => 4, 'vat' => 1, 'price' => 30));
        $product->save(array('name' => 'Complete CMS', 'unit' => 4, 'vat' => 1, 'price' => 60));
        
        
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
