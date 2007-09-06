<?php
/* 
 * This class is used by the automated script and makes sure to grant acces and remove access
 * according to the modulepackages an intranet has.
 */


class ModuleManagerAccessUpdate {
    
    private $kernel;
    
    function __construct($kernel) {
        
        $this->kernel = &$kernel;
    }
    
    static public function run() {
        
        $db = MDB2::singleton(DB_DSN);
        $package_removed = 0;
        $package_added = 0;
        
        // first we remove access to ended packages.
        $result = $db->query("SELECT id, intranet_id, module_package_id FROM intranet_module_package WHERE active = 1 AND status = 2 AND end_date < NOW()");
        if(PEAR::isError($result)) {
            trigger_error("Error in query for removing acces in ModulePackageManagerAccessUpdate->run :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        while($row = $result->fetchRow()) {
            $modulepackage = new ModulePackage($row['module_package_id']);
            
            // Hvordan får vi adgang til IntranetMaintenance?
            
            $intranet = new IntranetMaintenance($this->kernel, $row['intranet_id']);
            
            $modules = $modulepackage->get('modules');
            if(is_array($modules) && count($modules) > 0) {
                foreach($modules AS $module) {
                    $intranet->removeModuleAccess($module['module']);
                }
                
            }
            
            $package_removed += $this->db->exec('UPDATE intranet_module_package SET status = 3 WHERE id = '.$this->db->quote($row['id'], 'integer'));
        }
        
        
        
        // then we set access to new packages.
        $result = $db->query("SELECT intranet_id, module_package_id FROM intranet_module_package WHERE active = 1 AND status = 1 AND end_date < NOW()");
        if(PEAR::isError($result)) {
            trigger_error("Error in query for removing acces in ModulePackageManagerAccessUpdate->run :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        while($row = $result->fetchRow()) {
            $modulepackage = new ModulePackage($row['module_package_id']);
            
            // Hvordan får vi adgang til IntranetMaintenance?
            
            $intranet = new IntranetMaintenance($this->kernel, $row['intranet_id']);
            
            $modules = $modulepackage->get('modules');
            if(is_array($modules) && count($modules) > 0) {
                foreach($modules AS $module) {
                    $intranet->setModuleAccess($module['module']);
                }
                
            }
            
            $package_added += $this->db->exec('UPDATE intranet_module_package SET status = 2 WHERE id = '.$this->db->quote($row['id'], 'integer'));
        }
        
    }
}
?>
