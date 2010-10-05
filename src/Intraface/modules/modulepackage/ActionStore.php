<?php
/**
 * This package is used to store the action in the database, so that it can be executed when payment
 * is recieved
 * 
 * @package Intraface_modules_modulepackage
 * @author sune
 * @version 0.0.1
 */
class Intraface_modules_modulepackage_ActionStore {
    
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
     * 
     * @var string $identifier
     */
    private $identifier;
    
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
        
        if (!is_object($action) || strtolower(get_class($action)) != 'intraface_modules_modulepackage_action') {
            throw new Exception('First parameter in Intraface_modules_modulepackage_ActionStore::store should be an action object. Now it is: '.strtolower(get_class($action)));
            exit;
        }
        
        $action_serialized = serialize($action);
        
        $identifier = md5($action->getOrderIdentifier().$this->intranet_id.time());
        
        $result = $this->db->exec('INSERT INTO module_package_action SET ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').', ' .
                'identifier = '.$this->db->quote($identifier, 'text').', ' .
                'order_debtor_identifier = '.$this->db->quote($action->getOrderIdentifier(), 'text').',  ' .
                'date_created = NOW(), ' .
                'action = '.$this->db->quote($action_serialized, 'text').', ' .
                'active = 1');
        
        if (PEAR::isError($result)) {
            throw new Exception("Error in query in Intraface_modules_modulepackage_ActionStore->store(): ".$result->getUserInfo());
            exit;
        }
        
        $id = $this->db->lastInsertID();
        if (PEAR::isError($id)) {
            throw new Exception("Error in query in Intraface_modules_modulepackage_ActionStore->store: ".$id->getUserInfo());
            exit;
        }
        $this->id = $id;
        return $identifier;
    }
    
    /**
     * Restore an action on the basis of an earlier identifier.
     * 
     * @param string identifier
     * 
     * @return object Action
     */
    public function restore($identifier) 
    {
        
        $result = $this->db->query('SELECT id, action, identifier FROM module_package_action WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND ' .
                'identifier = '.$this->db->quote($identifier, 'text').' AND ' .
                'active = 1 ');
        
        if (PEAR::isError($result)) {
            throw new Exception("Error in query in Intraface_modules_modulepackage_ActionStore::restore(): ".$result->getUserInfo());
            return false;
        }
        
        if ($result->numRows() == 0) {
            return false;
        }
        
        $row = $result->fetchRow();
        
        if ($row['action'] != '') {
            $this->id = $row['id'];
            $this->identifier = $row['identifier'];
            require_once("Intraface/modules/modulepackage/Action.php");
            return unserialize($row['action']);
        }
        return false;
        
    }
    
    /**
     * Restore an action on the basis of an earlier identifier.
     * 
     * @param string identifier
     * 
     * @return object Action
     */
    static public function restoreFromIdentifier($db, $identifier) 
    {
        
        $result = $db->query('SELECT id, action, identifier FROM module_package_action WHERE ' .
                'identifier = '.$db->quote($identifier, 'text').' AND ' .
                'active = 1 ');
        
        if (PEAR::isError($result)) {
            throw new Exception("Error in query in Intraface_modules_modulepackage_ActionStore::restoreFromIdentifier(): ".$result->getUserInfo());
            return false;
        }
        
        if ($result->numRows() == 0) {
            return false;
        }
        
        $row = $result->fetchRow();
        
        if ($row['action'] != '') {
            require_once("Intraface/modules/modulepackage/Action.php");
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
        if ($this->id == 0) {
            return false;
        }
        
        $result = $this->db->exec('UPDATE module_package_action SET active = 0 WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND ' .
                'id = '.$this->db->quote($this->id, 'integer'));
        
        if (PEAR::isError($result)) {
            throw new Exception("Error in query in Intraface_modules_modulepackage_ActionStore::delete(): ".$result->getUserInfo());
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
    
/**
     * Returns the store identifier which is generated on store and restore.
     * 
     * @return string action store identifier
     */ 
    public function getIdentifier() {
        return $this->identifier;
    }
}

?>