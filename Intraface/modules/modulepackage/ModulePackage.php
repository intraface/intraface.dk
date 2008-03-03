<?php
/**
 * This class groups all modules in to different packages which can be added for each intranet
 * It makes it easier to control which modules each intranet has and for long time they have
 * paid for it.
 *
 * @package Intraface_ModulePackage
 * @author sune
 * @version 0.0.1
 */
class Intraface_ModulePackage extends Standard {

    /**
     * @var object database
     */
    private $db;

    /**
     * @var object dbquery
     */
    public $dbquery;

    /**
     * @var id id on modulepackage
     */
    protected $id;

    /**
     * @var object error
     */
    public $error;

    /**
     * Init function
     *
     * @param object kernel The kernel object
     * @param int id on a ModulePackage
     *
     * @return void
      */
    public function __construct($id = 0)
    {
        $this->db = MDB2::singleton(DB_DSN);
        $this->id = (int)$id;
        $this->error = New Error;

        if ($this->id != 0) {
            $this->load();
        }
    }

    /**
     * Loads information about the modulepackage
     *
     * @return integer id
     */
    public function load()
    {
        $result = $this->db->query("SELECT module_package.id, module_package.product_id, module_package_group.group_name AS ".$this->db->quoteIdentifier('group').", module_package_plan.plan, module_package_plan.plan_index, module_package_group.id AS group_id " .
                "FROM module_package " .
                "INNER JOIN module_package_group ON module_package.module_package_group_id = module_package_group.id " .
                "INNER JOIN module_package_plan ON module_package.module_package_plan_id = module_package_plan.id " .
                "WHERE module_package.id = ".$this->db->quote($this->id, 'integer'));

        if (PEAR::isError($result)) {
            trigger_error("Error in db query in ModulePackage->load(): ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        if (!$this->value = $result->fetchRow()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return $this->id;
        }

        $this->value['modules'] = $this->getModules();

        return $this->id;
    }

    /**
     * Returns modules in a modulepackage
     *
     * @param integer id Optional you can provide an id otherwise it get it takes modules for the current modulepackage.
     *
     * @return array Modules
     */
    private function getModules($id = 0)
    {
        if ($id == 0) {
            $id = $this->id;
        } else {
            $id = intval($id);
        }

        $result = $this->db->query("SELECT id, module, limiter " .
                "FROM module_package_module " .
                "WHERE module_package_module.module_package_id = ".$this->db->quote($id, 'integer'));

        if (PEAR::isError($result)) {
            trigger_error("Error in db query in ModulePackage->getmModules(): ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        $modules = array();
        $i = 0;

        while ($row = $result->fetchRow()) {
            $modules[$i] = $row;
            $modules[$i]['limiters'] = $this->parseLimiters($row['limiter'], $row['module']);

            $i++;
        }

        return $modules;
    }

    /**
     * Parse the serialized array of limiters that is saved in the database.
     * The limiters are checked whether they are valid accoring to the Intraface/config/setting_limiters.php definition
     *
     * @param string string_limiter The serialized array string
     * @param string module The identifier of the module that the limiters belongs to
     *
     * @return array Array with limiters.
     */
    private function parseLimiters($string_limiters, $module)
    {
        if (trim($string_limiters) == '') {
            return array();
        }

        $limiters = unserialize($string_limiters);
        if ($limiters === false) {
            trigger_error('Unable to unserialize string "'.$string_limiters.'" in ModulePackage->parseLimiters', E_USER_NOTICE);
            return array();
        }

        $_limiter = array();
        // the array is filled in with values in the include file.
        require('Intraface/config/setting_limiters.php');

        // We make sure it is valid limiters
        $i = 0;
        $return_limiters = array();

        foreach ($limiters AS $limiter => $limit) {
            if (isset($_limiter[$module][$limiter])) {
                $return_limiters[$i] = $_limiter[$module][$limiter];
                $return_limiters[$i]['limiter'] = $limiter;
                $return_limiters[$i]['limit'] = $limit;
                switch($return_limiters[$i]['limit_type']) {
                    case 'file_size':
                        $return_limiters[$i]['limit_readable'] = filesize_readable($limit);
                        break;
                }
                $i++;
             }
             else {
                trigger_error('limiter '.$limiter.' in tabel module_package_module for module '.$module.' is not valid!', E_USER_NOTICE);
             }
        }

        return $return_limiters;
    }

    /**
     * Creates the dbquery object.
     *
     * @param object kernel
     *
     * @return void
     */
    public function createDBQuery($kernel)
    {
        $this->dbquery = new DBQuery($kernel, 'module_package', 'module_package.active = 1');
        $this->dbquery->setJoin('INNER', 'module_package_group', 'module_package.module_package_group_id = module_package_group.id', 'module_package_group.active = 1');
        $this->dbquery->setJoin('INNER', 'module_package_plan', 'module_package.module_package_plan_id = module_package_plan.id', 'module_package_plan.active = 1');

    }

    /**
     * Returns a list of possible packages
     *
     * @param string list_type Can be either list or matrix.
     *
     * @return array containing packages
     */
    public function getList($list_type = 'list')
    {
        if (!in_array($list_type, array('list', 'matrix'))) {
            trigger_error('Invalid list type '.$list_type.' in ModulePackage->getList()', E_USER_ERROR);
            exit;
        }

        $this->dbquery->setSorting('module_package_group.sorting_index, module_package_plan.plan_index');

        $list = array();
        $product_ids = array();
        $i = 0;
        $db = $this->dbquery->getRecordset('module_package.id, module_package_plan.plan, module_package_plan.id AS plan_id, module_package.product_id, module_package_group.group_name, module_package_group.id AS group_id');
        while ($db->nextRecord()) {
             $list[$i]['id'] = $db->f('id');
             $list[$i]['plan'] = $db->f('plan');
             $list[$i]['plan_id'] = $db->f('plan_id');
             $list[$i]['group'] = $db->f('group_name');
             $list[$i]['group_id'] = $db->f('group_id');
             $list[$i]['product_id'] = $db->f('product_id');
             if ($db->f('product_id') != 0) {
                 $product_ids[] = $db->f('product_id');
             }
             $list[$i]['modules'] = $this->getModules($db->f('id'));
             $list[$i]['product'] = array();
             $i++;
        }

        // get all products in one request and add them to the array
        require_once('Intraface/modules/modulepackage/ShopExtension.php');
        $shopextension = new Intraface_ModulePackage_ShopExtension;
        $products = $shopextension->getProduct((array)$product_ids);

        // we apply the products to the aray
        if (is_array($products) && count($products) > 0) {
            for ($i = 0, $max = count($list); $i < $max; $i++) {
                 if ($list[$i]['product_id'] != 0) {
                     foreach ($products['products'] AS $product) {
                         if ($product['id'] == $list[$i]['product_id']) {
                             $list[$i]['product'] = $product;
                         }
                     }
                 }
            }
        }

        if ($list_type == 'list') {
            return $list;
        } elseif ($list_type == 'matrix') {
            $matrix = array();
            foreach ($list AS $entry) {
                $matrix[$entry['group_id']][$entry['plan_id']] = $entry;
            }
            return $matrix;
        }
    }

    /**
     * Returns the available plans
     *
     * @return array Array with plans
     */
    function getPlans()
    {
        $result = $this->db->query("SELECT id, plan FROM module_package_plan WHERE active = 1 ORDER BY plan_index");
        if (PEAR::isError($result)) {
            trigger_error('Error in db query: '.$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        return $result->fetchAll();
    }

    /**
     * Returns the available groups
     *
     * @return array Array with groups
     */
    function getGroups()
    {
        $result = $this->db->query("SELECT id, group_name AS ".$this->db->quoteIdentifier('group')." FROM module_package_group WHERE active = 1 ORDER BY sorting_index");
        if (PEAR::isError($result)) {
            trigger_error('Error in db query: '.$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        return $result->fetchAll();
    }
}