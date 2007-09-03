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
    public function __construct($kernel, $id = 0) {
        
        $this->db = MDB2::singleton(DB_DSN);
        $this->kernel = &$kernel;
        $this->id = (int)$id;
        
    }
    
    /**
     * Creates the dbquery object.
     * 
     * @return void
     */
    public function createDBQuery() {
        $this->dbquery = new DBQuery($this->kernel, 'module_package');
        $this->dbquery->setJoin('INNER', 'module_package_group', 'module_package.module_package_group_id = module_package_group.id', '');
        
    }
    
    
    /**
     * Add a package to an intranet
     * 
     * This function should maybe have been a part of intranet class, e.g. $intranet->addModulePackage, but how do we get that to work.
     */
    public function intranetAddModulePackage($package_id, $start_date, $end_date, $intranet_id = 0) {
        
    }
    
    /**
     * Returns an array of the packages that an intranet has.
     */
    public function intranetGetModulePackages($intranet_id = 0) {
        
        /*
        $result = $this->db->query('SELECT intranet_module_package.id, intranet_module_package.module_package_id, intranet_module_package.start_date, intranet_module_package.end_date, intranet_module_package.invoice_debtor.id, intranet_module_package.status FROM intranet_module_package WHERE intranet_id = '.$this->db->quote($intranet_id, 'integer'));
        if(PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        $modulepackages = $result->fetchAll(MDB2_FETCHMODE_ORDERED);
        
        return $modulepackages;
        */
        return array();
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
} 
 
?>
