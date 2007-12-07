<?php
/**
 * Still under development!
 */
    
class ModulePackage_Limit {
    
    /**
     * @var array limiters
     */
    private $limiters;
    
    public function __construct($intranet) {
        
        require_once 'Intraface/modules/modulepackage/Manager.php';
        $manager = new Intraface_ModulePackage_Manager($intranet);
        
        $manager->createDBQuery();
        $manager->dbquery->setFilter('status', 'active');
        $packages = $manager->getList();
        $limiters = array();
        require_once 'Intraface/modules/modulepackage/ModulePackage.php';
        foreach($packages AS $package) {
            $package_module = new ModulePackage($package['module_package_id']);
            $limiters = array_merge($limiters, $package_module->get('limiters'));
        }
        $this->limiters = $limiters;
        
        print_r($limiters);
        
    }
    
    
    public function check($limiter, $assert_value)
    {
        
    }
    
    
}
?>
