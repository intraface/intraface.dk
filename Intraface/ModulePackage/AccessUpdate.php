<?php
/* 
 * This class grants module access according to module packages an intranet has.
 * It is used by the automated script and is run on if there is any instant changes in modulepackages.
 * 
 * @package Intraface_ModulePackage
 * @author Sune Jensen
 * @version 0.0.1
 */


class Intraface_ModulePackage_AccessUpdate 
{
    
    /**
     * @var object
     */
    private $kernel;
    
    /**
     * Constructor
     * 
     * @param object kernel Kernel
     * 
     * @return void
     */
    function __construct($kernel) 
    {
        
        $this->kernel = &$kernel;
    }
    
    /**
     * Run the AccessUpdate and applies module access acording to module packages
     * 
     * @return boolean true on success, false on failure
     */
    public function run() {
        
        $db = MDB2::singleton(DB_DSN);
        $package_removed = 0;
        $package_added = 0;
        
        // TODO: Hvordan får vi adgang til IntranetMaintenance?
        
        // first we remove access to ended packages.
        $result = $db->query("SELECT id, intranet_id, module_package_id FROM intranet_module_package WHERE active = 1 AND status = 2 AND end_date < NOW()");
        if(PEAR::isError($result)) {
            trigger_error("Error in query for removing acces in ModulePackageManagerAccessUpdate->run :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        while($row = $result->fetchRow()) {
            $modulepackage = new ModulePackage($row['module_package_id']);
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
            $intranet = new IntranetMaintenance($this->kernel, $row['intranet_id']);
            
            $modules = $modulepackage->get('modules');
            if(is_array($modules) && count($modules) > 0) {
                foreach($modules AS $module) {
                    $intranet->setModuleAccess($module['module']);
                }
            }
            $package_added += $this->db->exec('UPDATE intranet_module_package SET status = 2 WHERE id = '.$this->db->quote($row['id'], 'integer'));
        }
        
        // Here it is possible to make a log over the updates.
        
        return true;
        
    }
}
?>
