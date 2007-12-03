<?php
/**
 * Helper functions for ModulePackage
 */

class Install_Helper_ModulePackage 
{
    
    
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
    
}

?>
