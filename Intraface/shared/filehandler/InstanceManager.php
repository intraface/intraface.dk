<?php
/**
 * This class manage the user instance types that it is possibe to get
 */


class InstanceManager extends Standard {
    
    /**
     * @var object error error objekt
     */
    public $error;
    
    /**
     * @var object db db connection
     */
    private $db;
    
    /**
     * @var integer id
     */
    private $type_key;
    
    /**
     * @var integer intranet_id
     */
    private $intranet_id;
    
    /**
     * init function
     * 
     * @param object kernel
     * @param object id
     */
    
    
    const MIN_CUSTOM_TYPE_KEY_VALUE = 1000;
    
    function __construct($kernel, $type_key = 0) {
        
        
        $this->error = new Error;
        $this->db = MDB2::singleton(DB_DSN);
        $this->type_key = (int)$type_key;
        $this->intranet_id = $kernel->intranet->get('id');
        
        if($this->type_key > 0) {
            $this->load();
        }
    }
    
    /**
     * load data about intance type
     */
    function load() {
        
        
        $standard_types = $this->getStandardTypes();
        foreach($standard_types AS $tmp_standard_type) {
            if($tmp_standard_type['type_key'] == $this->type_key) {
                $standard_type = $tmp_standard_type;
                break;
            }
        }
        
        $result = $this->db->query('SELECT name, type_key, max_width, max_height, resize_type_key FROM file_handler_instance_type WHERE intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND type_key = '.$this->db->quote($this->type_key, 'integer'));
        if(PEAR::isError($result)) {
            trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        if($result->numRows() > 0) {
            $custom_type = $row = $result->fetchRow();
        }
        if(isset($standard_type) && isset($custom_type)) {
            $this->value = array_merge($standard_type, $custom_type);
            $resize_types = $this->getResizeTypes();
            $this->value['resize_type'] = $resize_types[$this->value['resize_type_key']];
            $this->value['origin'] = 'overwritten';
        }
        elseif(isset($standard_type)) {
            $this->value = $standard_type;
            $this->value['origin'] = 'standard';           
        }
        elseif(isset($custom_type)) {
            $this->value = $custom_type;
            $resize_types = $this->getResizeTypes();
            $this->value['resize_type'] = $resize_types[$this->value['resize_type_key']];
            $this->value['origin'] = 'custom';
        }
        else {
            $this->type_key = 0;
            $this->value['id'] = 0;
            return false;
        }
    }
    
    /**
     * saves a new custom instance
     * 
     * @param array input array with instance that should be saved
     * @return boolean true on success or false on failure
     */
    function save($input) 
    {
        
        $validator = new Validator($this->error);
        
        if($this->type_key != 0) {
            $standard_types = $this->getStandardTypes();
            foreach($standard_types AS $standard_type) {
                if($standard_type['type_key'] == $this->type_key) {
                    // then we set the name to the standard type name
                    $input['name'] = $standard_type['name'];
                    if($standard_type['fixed']) {
                        trigger_error('You cannot overwrite fixed types', E_USER_ERROR);
                        return false;
                    }
                }
            }
        }
        
        $validator->isIdentifier($input['name'], 'invalid name', '');
        if(!$this->isNameFree($input['name'], $this->type_key)) {
            $this->error->set('an instance with the same name already exists');
        }
        
        
        $validator->isNumeric($input['max_width'], 'invalid max width', 'integer,greater_than_zero');
        $validator->isNumeric($input['max_height'], 'invalid max height', 'integer,greater_than_zero');
        if($validator->isString($input['resize_type'], 'error in resize type', '')) {
            $resize_types = $this->getResizeTypes();
            $resize_type_key = array_search($input['resize_type'], $resize_types);
            if($resize_type_key === false) {
                $this->error->set('invalid resize type');
            }
        }
        
        if($this->error->isError()) {
            return false;
        }
        
        $sql = 'name = '.$this->db->quote($input['name'], 'text').', ' .
                'max_width = '.$this->db->quote($input['max_width'], 'integer').', ' .
                'max_height = '.$this->db->quote($input['max_height'], 'integer').', ' .
                'resize_type_key = '.$this->db->quote($resize_type_key);
        
        if($this->type_key == 0 || $this->get('origin') == 'standard') {
            if($this->type_key == 0) {
                $type_key = $this->getNextFreeTypeKey();
            }
            else {
                $type_key = $this->type_key;
            }
            
            $result = $this->db->exec('INSERT INTO file_handler_instance_type SET ' .
                    'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').', ' .
                    'type_key = '.$this->db->quote($type_key, 'integer').', ' .
                    'active = 1, '.$sql);
            if(PEAR::isError($result)) {
                trigger_error('Error in exec: '.$result->getUserInfo(), E_USER_ERROR);
                return false;
            }
            if($result == 0) {
                $this->error->set('unable to save the instance');
                return false;
            }
            $this->type_key = $type_key;
            return $this->type_key;
        }
        else {
            $result = $this->db->exec('UPDATE file_handler_instance_type SET '.$sql.' ' .
                    'WHERE intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' ' .
                            'AND type_key = '.$this->db->quote($this->type_key, 'integer'));
            if(PEAR::isError($result)) {
                trigger_error('Error in exec: '.$result->getUserInfo(), E_USER_ERROR);
                return false;
            }
        }
        
        return $this->type_key;
        
    }
    
    /**
     * loads types
     *
     *
     * @return array
     */
    private function getStandardTypes() {
        return array(
            0 => array('type_key' => 0, 'name' => 'custom', 'fixed' => true, 'hidden' => true), // Manuelt størrelse
            1 => array('type_key' => 1, 'name' => 'square', 'fixed' => false, 'hidden' => false, 'max_width' => 75, 'max_height' => 75, 'resize_type' => 'strict'),
            2 => array('type_key' => 2, 'name' => 'thumbnail', 'fixed' => false, 'hidden' => false, 'max_width' => 100, 'max_height' => 67, 'resize_type' => 'relative'),
            3 => array('type_key' => 3, 'name' => 'small', 'fixed' => false, 'hidden' => false, 'max_width' => 240, 'max_height' => 160, 'resize_type' => 'relative'),
            4 => array('type_key' => 4, 'name' => 'medium', 'fixed' => false, 'hidden' => false, 'max_width' => 500, 'max_height' => 333, 'resize_type' => 'relative'),
            5 => array('type_key' => 5, 'name' => 'large', 'fixed' => false, 'hidden' => false, 'max_width' => 1024, 'max_height' => 683, 'resize_type' => 'relative'),
            6 => array('type_key' => 6, 'name' => 'website', 'fixed' => false, 'hidden' => false, 'max_width' => 780, 'max_height' => 550, 'resize_type' => 'relative'),
            7 => array('type_key' => 7, 'name' => 'system-square', 'fixed' => true, 'hidden' => true, 'max_width' => 75, 'max_height' => 75, 'resize_type' => 'strict'),
            8 => array('type_key' => 8, 'name' => 'system-thumbnail', 'fixed' => true, 'hidden' => true, 'max_width' => 100, 'max_height' => 67, 'resize_type' => 'relative'),
            9 => array('type_key' => 9, 'name' => 'system-small', 'fixed' => true, 'hidden' => true, 'max_width' => 240, 'max_height' => 160, 'resize_type' => 'relative'),
            10 => array('type_key' => 10, 'name' => 'system-medium', 'fixed' => true, 'hidden' => true, 'max_width' => 500, 'max_height' => 333, 'resize_type' => 'relative'),
            11 => array('type_key' => 11, 'name' => 'system-large', 'fixed' => true, 'hidden' => true, 'max_width' => 1024, 'max_height' => 683, 'resize_type' => 'relative')
        );
        
        
           
    }
    
    /**
     * Checks whether a name is free to use
     * 
     * @param string $name 
     * @param integer $id integer which should not be checked
     * @return boolean true or false
     */
    private function isNameFree($name, $type_key = 0) 
    {
        
        $standard_types = $this->getStandardTypes();
        foreach($standard_types AS $standard_type) {
            if($standard_type['name'] == $name && $standard_type['type_key'] != $type_key) {
                return false;
            }
        }
        
        $result = $this->db->query('SELECT type_key FROM file_handler_instance_type WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND ' .
                'name = '.$this->db->quote($name, 'text').' AND ' .
                'type_key != '.$this->db->quote((int)$type_key, 'integer').' AND ' .
                'active = 1');
        if(PEAR::isError($result)) {
            trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        
        if($result->numRows() > 0) {
            
            return false;
        }
        
        return true;
        
    }
    
    /**
     * Checks whether a name is free to use
     * 
     * @param string $name 
     * @param integer $id integer which should not be checked
     * @return boolean true or false
     */
    private function getNextFreeTypeKey() 
    {
        // We do not active = 1, then it is possible to recreate deleted items without messing everything up.
        $result = $this->db->query('SELECT MAX(type_key) AS max_key FROM file_handler_instance_type WHERE ' .
                'intranet_id = '.$this->db->quote($this->intranet_id, 'integer'));
        if(PEAR::isError($result)) {
            trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        if($row['max_key'] >= InstanceManager::MIN_CUSTOM_TYPE_KEY_VALUE) {
            return $row['max_key'] + 1;
        }
        else {
            return InstanceManager::MIN_CUSTOM_TYPE_KEY_VALUE; 
        }  
    }
    
    /**
     * returns a list of custom instances
     * 
     * @return array instance types
     */
    public function getList($show = 'visible') 
    {
        if(!in_array($show, array('visible', 'include_hidden'))) {
            trigger_error('First parameter to InstanceManager->getList should either be visibe or include_hidden', E_USER_ERROR);
            exit;
        }
        
        $result = $this->db->query('SELECT type_key, name, max_width, max_height, resize_type_key FROM file_handler_instance_type WHERE intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND active = 1 ORDER BY type_key');
        if(PEAR::isError($result)) {
            trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        $custom_types = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
        $standard_types = $this->getStandardTypes();
        $resize_types = $this->getResizeTypes();
        
        
        $type = array();
        $i = 0;
        $s = 0; // index for standard_types
        $c = 0; // index for custom_types;
        
        while(isset($standard_types[$s]) || isset($custom_types[$c])) {
            
            if(isset($standard_types[$s])) {
                if($standard_types[$s]['hidden'] && $show == 'visible') {
                    $s++;
                    CONTINUE;
                }
                
                if(isset($custom_types[$c]['type_key']) && $standard_types[$s]['type_key'] == $custom_types[$c]['type_key']) {
                    $type[$i] = array_merge($standard_types[$s], $custom_types[$c]);
                    $type[$i]['resize_type'] = $resize_types[$type[$i]['resize_type_key']];
                    $type[$i]['origin'] = 'overwritten';
                    $c++;
                }
                else {
                    $type[$i] = $standard_types[$s];
                    $type[$i]['origin'] = 'standard';
                }
            }
            else {
               $type[$i] = $custom_types[$c];
               $type[$i]['resize_type'] = $resize_types[$type[$i]['resize_type_key']];
               $type[$i]['origin'] = 'custom';
               $c++; 
            } 
            
            $i++;
            $s++;
            
            
        }
        
        return $type;
    }
    
    public function delete() {
        if($this->type_key == 0) {
            trigger_error('You can not delete an instancetype without setting a type_key!', E_USER_ERROR);
            return false;
        }
        
        $result = $this->db->exec('UPDATE file_handler_instance_type SET active = 0 WHERE intranet_id = '.$this->db->quote($this->intranet_id, 'integer').' AND type_key = '.$this->db->quote($this->type_key, 'integer'));
        if(PEAR::isError($result)) {
            trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        
        return $result > 0;
    }
    
    public function getResizeTypes() {
        return array(0 => 'relative', 1 => 'strict'); 
    }
 
}
?>
