<?php
/**
 * Class to manage which ModulePackages an intranet has.
 * Should this class be called Intraface_IntranetModulePackage as this is actually what it is.
 *
 * @package Intraface_ModulePackage
 * @author sune
 * @version 0.0.1
 */
class Intraface_ModulePackage_Manager extends Intraface_Standard {

    /**
     * @var object intranet
     */
    public $intranet;

    /**
     * @var object db
     */
    private $db;

    /**
     * @var object error
     */
    public $error;

    /**
     * @var object dbquery
     */
    public $dbquery;

    /**
     * @var integer id on intranet module package
     */
    private $id;

    /**
     * Constructor
     *
     * @param object intranet intranet object
     * @param integer id for intranet module package
     *
     * @return void
     */
    public function __construct($intranet, $id = 0)
    {
        // Should we rather just take intranet_id as parameter?
        $this->intranet = &$intranet;
        $this->db = MDB2::singleton(DB_DSN);
        $this->error =  new Intraface_Error;
        $this->id = (int)$id;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Loads information about intranet module package
     *
     * @return integer id
     */
    private function load() {

        if ($this->id == 0) {
            return false;
        }

        // TODO: This could actually easily use dbquery
        $result = $this->db->query('SELECT intranet_module_package.id, ' .
                'intranet_module_package.module_package_id, ' .
                'intranet_module_package.start_date, ' .
                'intranet_module_package.end_date, ' .
                'intranet_module_package.order_debtor_id, ' .
                'intranet_module_package.status_key, ' .
                'module_package_plan.plan, ' .
                'module_package_group.group_name, ' .
                'DATE_FORMAT(intranet_module_package.start_date, "%d-%m-%Y") AS dk_start_date, ' .
                'DATE_FORMAT(intranet_module_package.end_date, "%d-%m-%Y") AS dk_end_date ' .
            'FROM intranet_module_package ' .
            'INNER JOIN module_package ON intranet_module_package.module_package_id = module_package.id ' .
            'INNER JOIN module_package_plan ON module_package.module_package_plan_id = module_package_plan.id ' .
            'INNER JOIN module_package_group ON module_package.module_package_group_id = module_package_group.id ' .
            'WHERE intranet_id = '.$this->db->quote($this->intranet->get('id'), 'integer').' AND intranet_module_package.id = '.$this->db->quote($this->id, 'integer'));

        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->load() :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        if ($row = $result->fetchRow()) {
            $status_types = $this->getStatusTypes();
            $row['status'] = $status_types[$row['status_key']];
            $this->value = $row;
            return $this->id;
        }
    }

    /**
     * Creates db query
     *
     * @param object kernel
     *
     * @return void
     */
    public function createDBQuery($kernel)
    {
        $this->dbquery = new Intraface_DBQuery($kernel, 'intranet_module_package', 'intranet_module_package.active = 1 AND intranet_module_package.intranet_id = '.$this->intranet->get('id'));
        $this->dbquery->setJoin('INNER', 'module_package', 'intranet_module_package.module_package_id = module_package.id', '');
        $this->dbquery->setJoin('INNER', 'module_package_group', 'module_package.module_package_group_id = module_package_group.id', '');
        $this->dbquery->setJoin('INNER', 'module_package_plan', 'module_package.module_package_plan_id = module_package_plan.id', '');
    }

    /**
     * Add a package to an intranet
     * This function should maybe have been a part of intranet class, e.g. $intranet->addModulePackage, but how do we get that to work.
     *
     * @param integer package_id id on modulepackage
     * @param string start_date The start date of the package in the pattern dd-mm-yyyy
     * @param string duration The duration as either date 'dd-mm-yyyy' or 'yyyy-mm-dd' or a 'X month' where X can be any whole number
     *
     * @return integer id on intranet module package
     */
    public function save($package_id, $start_date, $duration)
    {
        $modulepackage = new Intraface_ModulePackage(intval($package_id));

        if ($modulepackage->get('id') == 0) {
            $this->error->set('Invalid module package in');
        }

        $validator = new Intraface_Validator($this->error);

        if (!$validator->isDate($start_date, 'Invalid start date')) {
            return false;
        } else {
            $start_date = new Intraface_Date($start_date);
            $start_date->convert2db();
            $start_date = $start_date->get();
        }

        $parsed_duration = $this->parseDuration($start_date, $duration);
        $end_date = $parsed_duration['end_date'];

        // We make sure that it is not possible to add a package before existing is finished.
        if (strtotime($this->getLastEndDateInGroup($modulepackage)) >= strtotime($start_date)) {
            $this->error->set('you are trying to add a package in a group before the existing package is finished');
            trigger_error('you are trying to add a package in a group before the existing package is finished', E_USER_NOTICE);
            return false;
        }

        if ($this->error->isError()) {
            return false;
        }

        $sql = "module_package_id = ".$this->db->quote($modulepackage->get('id'), 'integer').", " .
                "start_date = ".$this->db->quote($start_date, 'date').", " .
                "end_date = ".$this->db->quote($end_date, 'date');

        $result = $this->db->exec("INSERT INTO intranet_module_package SET ".$sql.", status_key = 1, active = 1, intranet_id = ".$this->intranet->get('id'));
        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->save from result: ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        $id = $this->db->lastInsertID();
        if (PEAR::isError($id)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->save from id: ".$id->getUserInfo(), E_USER_ERROR);
            exit;
        }
        $this->id = $id;
        return $this->id;

    }

    /**
     * Attach an order id to an intranet module package
     *
     * @param integer order_id Id on the order created as a part of adding the package
     *
     * @return mixed Number of affected rows or false on error
     */
    public function addOrderId($order_id)
    {

        if (intval($order_id) == 0) {
            return false;
        }

        if ($this->id == 0) {
            return false;
        }

        $result = $this->db->exec('UPDATE intranet_module_package ' .
            'SET order_debtor_id = '.$this->db->quote($order_id, 'integer').' ' .
            'WHERE intranet_id = '.$this->db->quote($this->intranet->get('id'), 'integer').' AND id = '.$this->db->quote($this->id, 'integer'));

        if (PEAR::isError($result)) {
            trigger_error('Error in query:'.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }

        return $result;
    }

    /**
     * Set an intranet module package to be terminated
     *
     * @return boolean true or false
     */
    public function terminate()
    {
        if ($this->id == 0) {
            return false;
        }

        $result = $this->db->exec("UPDATE intranet_module_package SET status_key = 3 WHERE intranet_id = ".$this->intranet->get('id')." AND id = ".intval($this->id));
        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->terminate :". $result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        return $result;
    }

    /**
     * Delete an intranet module package
     *
     * @return boolean true or false
     */
    public function delete()
    {
        if ($this->id == 0) {
            return false;
        }
        $result = $this->db->exec("UPDATE intranet_module_package SET active = 0 WHERE intranet_id = ".$this->intranet->get('id')." AND id = ".intval($this->id));
        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->delete :". $result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        return $result;
    }

    /**
     * Returns on the basis of the module package which type of add this is. Can be either add, extend, or upgrade
     *
     * @param object modulepackage An modulepackage object
     *
     * @return string either 'add', 'extend' or 'upgrade'
     */
    public function getAddType($modulepackage)
    {
        if (!is_object($modulepackage) || strtolower(get_class($modulepackage)) != 'intraface_modulepackage') {
            trigger_error("Intraface_ModulePackage_Manager->getAddType needs object Intraface_ModulePackage as first parameter");
            exit;
        }

        if ($modulepackage->get('id') == 0) {
            trigger_error('module package id is not valid in Intraface_ModulePackage_Manager->getAddType', E_USER_ERROR);
            exit;
        }

        // TODO: If DBQuery had not needed kernel we could have used it here!
        $result = $this->db->query('SELECT module_package_plan.plan_index FROM intranet_module_package ' .
                'INNER JOIN module_package ON intranet_module_package.module_package_id = module_package.id ' .
                'INNER JOIN module_package_plan ON module_package.module_package_plan_id = module_package_plan.id ' .
                'WHERE intranet_module_package.intranet_id = '.$this->intranet->get('id').' AND intranet_module_package.active = 1 AND (intranet_module_package.status_key = 1 OR intranet_module_package.status_key = 2) AND module_package.module_package_group_id = '.$modulepackage->get('group_id').' ' .
                'ORDER BY end_date DESC');

        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->getAddType: ".$result->getUserInfo());
            exit;
        }

        if ($row = $result->fetchRow()) {
            if ($modulepackage->get('plan_index') > $row['plan_index']) {
                return 'upgrade';
            } else {
                return 'extend';
            }
        } else {
            return 'add';
        }
    }

    /**
     * Returns the end date of the last modulepacke in a given group
     *
     * @param object modulepackage
     */
    public function getLastEndDateInGroup($modulepackage)
    {

        if (!is_object($modulepackage) || strtolower(get_class($modulepackage)) != 'intraface_modulepackage') {
            trigger_error("Intraface_ModulePackage_Manager->getLastEndDateInGroup needs object Intraface_ModulePackage as Parameter");
            exit;
        }

        if ($modulepackage->get('id') == 0) {
            trigger_error('module package id is not valid in Intraface_ModulePackage_Manager->getLastEndDateInGroup', E_USER_ERROR);
            exit;
        }

        // TODO: If DBQuery had not needed kernel we could have used it here!
        $result = $this->db->query('SELECT intranet_module_package.end_date FROM intranet_module_package ' .
                'INNER JOIN module_package ON intranet_module_package.module_package_id = module_package.id ' .
                'INNER JOIN module_package_plan ON module_package.module_package_plan_id = module_package_plan.id ' .
                'WHERE intranet_module_package.intranet_id = '.$this->intranet->get('id').' AND intranet_module_package.active = 1 AND (intranet_module_package.status_key = 1 OR intranet_module_package.status_key = 2) AND module_package.module_package_group_id = '.$modulepackage->get('group_id').' ' .
                'ORDER BY end_date DESC');

        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->getLastEndDateInGroup: ".$result->getUserInfo());
            exit;
        }

        if ($row = $result->fetchRow()) {
            return $row['end_date'];
        } else {
            // yesterday!
            return date('Y-m-d', strtotime('-1 day', time()));
        }
    }

    /**
     * By providing a start date and a duration it returns an end date and a number of month
     *
     * @param string start_date the start date of the periode yyyy-mm-dd
     * @param string duration the duration as either an end date yyyy-mm-dd or dd-mm-yyyy or 'X month' where X can be any whole number
     *
     * @return array array containing end_date and month
     */
    private function parseDuration($start_date, $duration)
    {

        // first we check for danish format of duration
        if (ereg('^[0-9]{2}-[0-9]{2}-[0-9]{4}$', $duration)) {
            $validator = new Intraface_Validator($this->error);
            if ($validator->isDate($duration, 'Invalid end date')) {
                $end_date = new Intraface_Date($duration);
                $end_date->convert2db();
                $duration = $end_date->get();
            }
        }

        if (ereg('^[0-9]{4}-[0-9]{2}-[0-9]{2}$', $duration)) {

            // firsts we translate the duration into an integer
            $end_date_integer = strtotime($duration);
            $start_date_integer = strtotime($start_date);

            // then we find the number of month left.
            $month = 0;
            // first we add a month to see if there is a full month left.
            // If we want to give them this month for free this should be removed.
            $running_start_date_integer = strtotime('+1 month', $start_date_integer);
            while($running_start_date_integer <= $end_date_integer) {
                $running_start_date_integer = strtotime('+1 month', $running_start_date_integer);
                $month++;
            }

            return array('end_date' => $duration,
                'month' => $month);

        } elseif (ereg('^([0-9]{1,2}) month$', $duration, $params)) {

            if (intval($params[1]) == 0) {
                $this->error->set('The duration in month should be higher than zero.');
            }

            /*
            // The nice an easy way, but first from  PHP5.2
            $end_date = new DateTime($start_date->get());
            $end_date->modify('+'.intval($params[1]).' month');
            // $end_date->format('d-m-Y')
            */

            $end_date_integer = strtotime('+'.intval($params[1]).' month', strtotime($start_date));

            $end_date = new Intraface_Date(date('d-m-Y', $end_date_integer));
            $end_date->convert2db();

            return array('end_date' => $end_date->get(),
                'month' => $params[1]);
        } else {
            $this->error->set('Duration does not have a valid pattern in Intraface_ModulePackage_Manager->parseDuration', E_USER_ERROR);
            return array();
        }
    }

    /**
     * Adds a module package if there is no one already in the group
     *
     * @param object modulepackage module package
     * @param string duration Duration as either date 'dd-mm-yyyy' or 'yyyy-mm-dd' or 'X month' where X is any whole number
     *
     * @return object Action object with the actions needed to be processed
     */
    public function add($modulepackage, $duration)
    {
        if (!is_object($modulepackage) || strtolower(get_class($modulepackage)) != 'intraface_modulepackage') {
            trigger_error('Intraface_ModulePackage_Manager->add needs object Intraface_ModulePackage as Parameter', E_USER_ERROR);
            exit;
        }

        if ($modulepackage->get('id') == 0) {
            trigger_error('module package id is not valid in Intraface_ModulePackage_Manager->add', E_USER_ERROR);
            exit;
        }

        // extends makes the same process
        return $this->extend($modulepackage, $duration);
    }

    /**
     * Extends with the given module package if there is already a module packe in the group
     *
     * @param object modulepackage
     * @param string duration Duration as either date 'dd-mm-yyyy' or 'yyyy-mm-dd' or 'X month' where X is any whole number
     *
     * @return object Action object with the actions needed to be performed
     */
    public function extend($modulepackage, $duration)
    {
        if (!is_object($modulepackage) || strtolower(get_class($modulepackage)) != 'intraface_modulepackage') {
            trigger_error('Intraface_ModulePackage_Manager->extend needs object Intraface_ModulePackage as Parameter', E_USER_ERROR);
            exit;
        }

        if ($modulepackage->get('id') == 0) {
            trigger_error('module package id is not valid in Intraface_ModulePackage_Manager->extend', E_USER_ERROR);
            exit;
        }


        $start_date = date('Y-m-d', strtotime('+1 day', strtotime($this->getLastEndDateInGroup($modulepackage))));
        $parsed_duration = $this->parseDuration($start_date, $duration);
        $end_date = $parsed_duration['end_date'];
        $month = $parsed_duration['month'];

        if ($this->error->isError()) {
            return array();
        }

        require_once('Intraface/modules/modulepackage/Action.php');
        $action = new Intraface_ModulePackage_Action;

        $action->addAction(array(
            'action' => 'add',
            'module_package_id' => $modulepackage->get('id'),
            'product_id' => $modulepackage->get('product_id'),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'month' => $month));


        return $action;

    }

    /**
     * Upgrades to the given module package if there is already a module packe in the group
     *
     * @param object modulepackage
     * @param string duration Duration as either date 'dd-mm-yyyy' or 'yyyy-mm-dd' or 'X month' where X is any whole number
     *
     * @return object Action object with the actions needed to be performed
     */
    public function upgrade($modulepackage, $duration)
    {
        if (!is_object($modulepackage) || strtolower(get_class($modulepackage)) != 'intraface_modulepackage') {
            trigger_error('Intraface_ModulePackage_Manager->upgrade needs object Intraface_ModulePackage as Parameter', E_USER_ERROR);
            exit;
        }

        if ($modulepackage->get('id') == 0) {
            trigger_error('module package id is not valid in Intraface_ModulePackage_Manager->upgrade', E_USER_ERROR);
            exit;
        }

        $start_date = date('Y-m-d');
        $parsed_duration = $this->parseDuration($start_date, $duration);

        if ($this->error->isError()) {
            return array();
        }

        require_once('Intraface/modules/modulepackage/Action.php');
        $action = new Intraface_ModulePackage_Action;

        require_once('Intraface/modules/modulepackage/ShopExtension.php');
        $shop = new Intraface_ModulePackage_ShopExtension;

        // TODO: If DBQuery had not needed kernel we could have used it here!
        $result = $this->db->query('SELECT intranet_module_package.id, intranet_module_package.status_key, intranet_module_package.start_date, intranet_module_package.end_date, intranet_module_package.order_debtor_id, intranet_module_package.module_package_id, module_package.product_id ' .
                'FROM intranet_module_package ' .
                'INNER JOIN module_package ON intranet_module_package.module_package_id = module_package.id ' .
                'INNER JOIN module_package_plan ON module_package.module_package_plan_id = module_package_plan.id ' .
                'WHERE intranet_module_package.intranet_id = '.$this->intranet->get('id').' AND intranet_module_package.active = 1 AND (intranet_module_package.status_key = 1 OR intranet_module_package.status_key = 2) AND module_package.module_package_group_id = '.$modulepackage->get('group_id').' ' .
                'ORDER BY end_date DESC');

        if (PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Manager->upgrade: ".$result->getUserInfo());
            exit;
        }

        while($row = $result->fetchRow()) {

            $add_action = array();

            if ($row['status_key'] == 1) { // 'created'
                $add_action['action'] = 'delete';
                // the whole package is left, we substract the whole package.
                // the start date as integer
                $remaining_start_date = $row['start_date'];
            } elseif ($row['status_key'] == 2) { // 'active'
                $add_action['action'] = 'terminate';
                // the package is in use. We substract from today
                // the start date as integer
                $remaining_start_date = date('Y-m-d');
            }

            $remaining = $this->parseDuration($remaining_start_date, $row['end_date']);
            $add_action['month'] = $remaining['month'];
            $add_action['order_debtor_id'] = $row['order_debtor_id'];
            $add_action['intranet_module_package_id'] = $row['id'];


            if ($row['order_debtor_id'] != 0 && $modulepackage->get('product_id') != 0) {
                $product = $shop->getProductDetailFromExistingOrder($row['order_debtor_id'], $row['product_id']);
                $add_action['product_detail_id'] = $product['product_detail_id'];
                $add_action['product_id'] = $row['product_id'];

            } else {
                $add_action['product_detail_id'] = 0;
                $add_action['product_id'] = 0;
            }

            $action->addAction($add_action);

        }


        // we add the add package action
        $action->addAction(array('action' => 'add',
            'module_package_id' => $modulepackage->get('id'),
            'product_id' => $modulepackage->get('product_id'),
            'start_date' => $start_date,
            'end_date' => $parsed_duration['end_date'],
            'month' => $parsed_duration['month']));

        return $action;
     }

    /**
     * Returns an array of the packages that an intranet has.
     * This can be modified with dbquery
     *
     * @return array Array with the modulepackages
     */
    public function getList()
    {
        if (!isset($this->dbquery) || !is_object($this->dbquery)) {
            trigger_error("DBQuery needs to be initiated before use of getList in Intraface_ModulePackage_Manager->getList", E_USER_ERROR);
        }

        if ($this->dbquery->checkFilter('status')) {
            if ($this->dbquery->getFilter('status') == 'created_and_active') {
                $this->dbquery->setCondition('status_key = 1 OR status_key = 2');
            } elseif ($this->dbquery->getFilter('status') == 'active') {
                $this->dbquery->setCondition('status_key = 2');
            }

        }

        if ($this->dbquery->checkFilter('group_id')) {
            $this->dbquery->setCondition('module_package_group.id = '.(int)$this->dbquery->getFilter('group_id'));
        }

        if ($this->dbquery->checkFilter('sorting')) {
            if ($this->dbquery->getFilter('sorting') == 'end_date') {
                $this->dbquery->setSorting('intranet_module_package.end_date');
            }
        } else {
            $this->dbquery->setSorting('module_package_group.sorting_index, module_package_plan.plan_index, intranet_module_package.start_date');
        }

        $db = $this->dbquery->getRecordset('intranet_module_package.id, ' .
                    'intranet_module_package.module_package_id, ' .
                    'intranet_module_package.start_date, ' .
                    'intranet_module_package.end_date, ' .
                    'intranet_module_package.order_debtor_id, ' .
                    'intranet_module_package.status_key, ' .
                    'module_package_plan.plan, ' .
                    'module_package_group.group_name, ' .
                    'DATE_FORMAT(intranet_module_package.start_date, "%d-%m-%Y") AS dk_start_date, ' .
                    'DATE_FORMAT(intranet_module_package.end_date, "%d-%m-%Y") AS dk_end_date', '', false);

        $modulepackages = array();
        $i = 0;

        $status_types = $this->getStatusTypes();
        while($db->nextRecord()) {
            $modulepackages[$i]['id'] = $db->f('id');
            $modulepackages[$i]['module_package_id'] = $db->f('module_package_id');
            $modulepackages[$i]['start_date'] = $db->f('start_date');
            $modulepackages[$i]['dk_start_date'] = $db->f('dk_start_date');
            $modulepackages[$i]['end_date'] = $db->f('end_date');
            $modulepackages[$i]['dk_end_date'] = $db->f('dk_end_date');
            $modulepackages[$i]['order_debtor_id'] = $db->f('order_debtor_id');
            $modulepackages[$i]['status_key'] = $db->f('status_key');
            $modulepackages[$i]['status'] = $status_types[$db->f('status_key')];
            $modulepackages[$i]['plan'] = $db->f('plan');
            $modulepackages[$i]['group'] = $db->f('group_name');
            $i++;
        }

        return $modulepackages;
    }

    /**
     * Returns the possible types of status'
     *
     * @return array status types
     */
    static public function getStatusTypes()
    {
        return array(0 => '_invalid_',
            1 => 'created',
            2 => 'active',
            3 => 'terminate',
            4 => 'used');
    }
}

?>