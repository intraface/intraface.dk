<?php
/*
 * This class groups all modules in to different packages which can be added for each intranet
 * It makes it easier to control which modules each intranet has and for long time they have
 * paid for it.
 **/

/*

Ideen er at vi skal have følgende pakker:

Hjemmeside (cms, filehandler)
Regnskab (accounting)
Virksomhedsstyring (debtor, contact, products, osv.)

Hver pakke har følgende udgaver:
Gratis (ex. oprettelse af 1 side om måneden, upload 2mb)
Mellem (ex. oprettelse af 5 sider om måneden, upload 10mb)
Stor (ubegrænsede sider, 100 mb upload om måneden)

Så bliver en pakke tilføjet til intranettet, i en bestemt udgave, med en startdato og en slutdato.
Hver nat kører vi et script, der giver adgang til de moduler, de har i pakken og fjerner adgang til
moduler i de pakker der udløber.

*/ 

require_once('Intraface/ModulePackageManager.php');

class ModulePackage extends Standard {
    
    private $db;
    protected $kernel;
    public $dbquery;
    protected $id;
    
    /**
     * Init function
     * 
     * @param object kernel The kernel object
     * @param int id on a ModulePackage
      */
    public function __construct($id = 0) {
        
        $this->db = MDB2::singleton(DB_DSN);
        $this->id = (int)$id;
        
        if($this->id != 0) {
            $this->load();
        }
        
        
        
    }
    
    public function load() {
        
        $result = $this->db->query("SELECT module_package.id, module_package.name, module_package.product_id, module_package_group.name AS group_name " .
                "FROM module_package INNER JOIN module_package_group ON module_package.module_package_group_id = module_package_group.id " .
                "WHERE module_package.id = ".$this->db->quote($this->id, 'integer'));
        
        if(PEAR::isError($result)) {
            trigger_error("Error in db query in ModulePackage->load(): ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        if(!$this->value = $result->fetchRow()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return $this->id;
        }
        
        $result = $this->db->query("SELECT id, module, limiter " .
                "FROM module_package_module " .
                "WHERE module_package_module.module_package_id = ".$this->db->quote($this->id, 'integer'));
        
        if(PEAR::isError($result)) {
            trigger_error("Error in db query in ModulePackage->load(): ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        $this->value['modules'] = $result->fetchAll();
        
        return $this->id;
    }
    
    /**
     * Creates the dbquery object.
     * 
     * @return void
     */
    public function createDBQuery($kernel) {
        $this->dbquery = new DBQuery($kernel, 'module_package');
        $this->dbquery->setJoin('INNER', 'module_package_group', 'module_package.module_package_group_id = module_package_group.id', '');
        
    }
    
    /**
     * Returns a list of possible packages
     * 
     * @return array containing packages
     */
    function getList() {
        
        $list = array();
        $i = 0;        
        $db = $this->dbquery->getRecordset('module_package.id, module_package.name, module_package.product_id, module_package_group.name AS group_name');
        while($db->nextRecord()) {
             $list[$i]['id'] = $db->f('id');
             $list[$i]['name'] = $db->f('name');
             $list[$i]['group_name'] = $db->f('group_name');
             $list[$i]['product_id'] = $db->f('product_id');
             $i++;
        }
        return $list;
    }
    
    static public function getStatusTypes() {
        return array(0 => '_invalid_',
            1 => 'created',
            2 => 'active',
            3 => 'used');
    }
} 
 
?>
