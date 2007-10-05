<?php
/**
 * This package is used to store the action in the database, so that it can be executed when payment
 * is recieved
 * 
 * @package Intraface_ModulePackage
 * @author sune
 * @version 0.0.1
 */
class Intraface_ModulePackage_ActionStore {
    
    /**
     * @var integer intranet id
     */
    private $intranet_id;
    
    /**
     * @var object database
     */
    private $db;
    
    /**
     * @var id Id on action store, is set on restore.
     */
    private $id;
    
    /**
     * Constructor
     * 
     * @param integer intranet_id
     * 
     * @return void 
     */
    function __construct($intranet_id) {
        $this->intranet_id = (int)$intranet_id;
        $this->db = MDB2::singleton(DB_DSN);
        $this->id = 0;
    }
    
    
    /**
     * Store the action object in the database
     * 
     * @param object action     An action object
     * 
     * @return boolean true or false
     */
    public function store($action) 
    {
        
        if(!is_object($action) || strtolower(get_class($action)) != 'intraface_modulepackage_action') {
            trigger_error('First parameter in Intraface_ModulePackage_ActionStore::store should be an action object. Now it is: '.strtolower(get_class($action)), E_USER_ERROR);
            exit;
        }
        
        $action_serialized = serialize($action);
        
        $result = $this->db->exec('INSERT INTO module_package_action SET ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').', ' .
                'order_debtor_id = '.$this->db->quote($action->getOrderId(), 'integer').',  ' .
                'date_created = NOW(), ' .
                'action = '.$this->db->quote($action_serialized, 'text').', ' .
                'active = 1');
        
        if(PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Action::store(): ".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        
        $id = $this->db->lastInsertID();
        if (PEAR::isError($id)) {
            trigger_error("Error in query in Intraface_ModulePackage_ActionStore->store: ".$id->getUserInfo(), E_USER_ERROR);
            exit;
        }
        $this->id = $id;
        return $this->id;
    }
    
    /**
     * Restore an action on the basis of an earlier id.
     * 
     * @param integer id
     * 
     * @return object Action
     */
    public function restore($id) 
    {
        
        $result = $this->db->query('SELECT id, action FROM module_package_action WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND ' .
                'id = '.$this->db->quote($id, 'integer').' AND ' .
                'active = 1 ');
        
        if(PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Action::restore(): ".$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        
        $row = $result->fetchRow();
        
        if($row['action'] != '') {
            $this->id = $row['id'];
            require_once("Intraface/ModulePackage/Action.php");
            return unserialize($row['action']);
        }
        return false;
        
    }
    
    /**
     * Restore an Action object from the database on the basis of intranet id, as in onlinepayment order id is the only form of identification
     * 
     * @param integer order_id Id on the order attached to the action
     */
    public function restoreFromOrderId($order_id) 
    {
        
        $result = $this->db->query('SELECT id, action FROM module_package_action WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND ' .
                'order_debtor_id = '.$this->db->quote($order_id, 'integer').' AND ' .
                'active = 1 ' .
                'ORDER BY id DESC LIMIT 1'); // could there be a slight chance that there are more than one action per order_id? probably not!
        
        if(PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_Action::restoreFromOrderId(): ".$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        
        $row = $result->fetchRow();
        
        if($row['action'] != '') {
            $this->id = $row['id'];
            require_once("Intraface/ModulePackage/Action.php");
            return unserialize($row['action']);
        }
        return false;
        
    }
    
    /**
     * Delete an stored action from the database
     * 
     * @param integer id
     * 
     * @return boolean true or false
     */
    public function delete() 
    {
        if($this->id == 0) {
            return false;
        }
        
        $result = $this->db->exec('UPDATE module_package_action SET active = 0 WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND ' .
                'id = '.$this->db->quote($this->id, 'integer'));
        
        if(PEAR::isError($result)) {
            trigger_error("Error in query in Intraface_ModulePackage_ActionStore::delete(): ".$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        
        return $result;
    }
    
    /**
     * Returns the store id which is generated on store and restore.
     * 
     * @return integer action store id
     */ 
    public function getId() {
        return $this->id;
    }
}

?>