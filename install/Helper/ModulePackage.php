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
    
    public function addModulePackageExperingWithinAMonthForAlternativIntranet() 
    {
        $intranet = new Intraface_Intranet(2);
        
        require_once 'Intraface/modules/modulepackage/Manager.php';
        $manager = new Intraface_modules_modulepackage_Manager($intranet);
        
        require_once 'Intraface/modules/modulepackage/ModulePackage.php';
        $modulepackage = new Intraface_modules_modulepackage_ModulePackage(1); // free cms
        
        $action = $manager->add($modulepackage, date('Y-m-d', strtotime('+14 days')));
        $action->execute($intranet);
        
        require_once 'Intraface/modules/modulepackage/AccessUpdate.php';
        $access_update = new Intraface_modules_modulepackage_AccessUpdate(); 
        $access_update->run(2);
        
    } 
    
}

?>
