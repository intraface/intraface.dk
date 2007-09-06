<?php
/*
 * Class to manage which ModulePackages an intranet has.
 */

class ModulePackageManager extends Standard {
    
    public $intranet;
    private $db;
    public $error;
    
    
    public function __construct($intranet) {
        
        $this->intranet = &$intranet;
        $this->db = MDB2::singleton(DB_DSN);
        $this->error =  new Error;
        
        
    }
    
        /**
     * Add a package to an intranet
     * 
     * This function should maybe have been a part of intranet class, e.g. $intranet->addModulePackage, but how do we get that to work.
     */
    public function addModulePackage($package_id, $start_date, $duration) {
        
        $modulepackage = new ModulePackage(intval($package_id));
        
        if($modulepackage->get('id') == 0) {
            $this->error->set('Invalid module package in');
        }
        
        // require_once('Intraface/tools/Date.php');
        
        $validator = new Validator($this->error);
        
        if(!$validator->isDate($start_date, 'Invalid start date')) {
            return false;
        }
        else {    
            $start_date = new Intraface_Date($start_date);
            $start_date->convert2db();
        }
        
        
        if(ereg('^[0-9]{4}-[0-9]{2}-[0-9]{2}$', $duration)) {
            if($validator->isDate($duration, 'Invalid end date')) {
                $end_date = new Intraface_Date($duration);
                $end_date->convert2db();
            }
        }
        elseif(ereg('^([0-9]{1,2}) month$', $duration, $params)) {
            
            if(intval($params[1]) == 0) {
                $this->error->set('The duration in month should be higher than zero.');
            }
            
            /*
            // The nice an easy way, but first from  PHP5.2
            $end_date = new DateTime($start_date->get());
            $end_date->modify('+'.intval($params[1]).' month');
            // $end_date->format('d-m-Y')
            */
            
            $end_date_integer = strtotime('+'.intval($params[1]).' month', strtotime($start_date->get()));
            
            $end_date = new Intraface_Date(date('d-m-Y', $end_date_integer));
            $end_date->convert2db();
        }
        
        
        if($this->error->isError()) {
            return false;
        }
        
        $sql = "module_package_id = ".$this->db->quote($modulepackage->get('id'), 'integer').", " .
                "start_date = ".$this->db->quote($start_date->get(), 'date').", " .
                "end_date = ".$this->db->quote($end_date->get(), 'date');
        
        $result = $this->db->exec("INSERT INTO intranet_module_package SET ".$sql.", status_key = 1, active = 1, intranet_id = ".$this->intranet->get('id'));
        if (PEAR::isError($result)) {
            trigger_error("Error in query in ModulePackageManager->addModulePackage from result: ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        $id = $this->db->lastInsertID();
        if (PEAR::isError($id)) {
            trigger_error("Error in query in ModulePackageManager->addModulePackage from id: ".$id->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        return $id;
        
    }
    
    /**
     * Returns an array of the packages that an intranet has.
     */
    public function getModulePackages() {
        
        $result = $this->db->query('SELECT ' .
                'intranet_module_package.id, ' .
                'intranet_module_package.module_package_id, ' .
                'intranet_module_package.start_date, ' .
                'intranet_module_package.end_date, ' .
                'intranet_module_package.invoice_debtor_id, ' .
                'intranet_module_package.status_key, ' .
                'module_package.name, ' .
                'module_package_group.name AS group_name ' .
            'FROM intranet_module_package ' .
            'INNER JOIN module_package ON intranet_module_package.module_package_id = module_package.id ' .
            'INNER JOIN module_package_group ON module_package.module_package_group_id = module_package_group.id ' .
            'WHERE intranet_module_package.active = 1 AND intranet_id = '.$this->db->quote($this->intranet->get('id'), 'integer'));
        if(PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        $modulepackages = array();
        $i = 0;
        
        $status_types = ModulePackage::getStatusTypes();
        
        while($row = $result->fetchRow()) {
            $modulepackages[$i] = $row;
            $modulepackages[$i]['status'] = $status_types[$row['status_key']];
            $i++;
        }
        
        return $modulepackages;
        
    }
    
    public function deleteModulePackage($id) {
        
        $result = $this->db->exec("UPDATE intranet_module_package SET active = 0 WHERE intranet_id = ".$this->intranet->get('id')." AND id = ".intval($id));
        if(PEAR::isError($result)) {
            trigger_error("Error in query in ModulePackageManager->deleteModulePackage :". $result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        return $result;
    }
    
    static public function runAccessUpdate($kernel) {
        
        // first we remove access to ended packages.
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query("SELECT intranet_id, module_package_id FROM intranet_module_package WHERE active = 1 AND status = 2 AND end_date < NOW()");
        if(PEAR::isError($result)) {
            trigger_error("Error in query for removing acces in ModulePackageManager::runAccessUpdate :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        while($row = $result->fetchRow()) {
            $modulepackage = new ModulePackage($row['module_package_id']);
            // her stoppede det sjove, hvordan får vi adgang til denne funktionalitet.
            // $intranet = new IntranetMaint
        }
        
    }
}

?>