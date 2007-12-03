<?php

class Install_Helper_IntranetMaintenance {
    
    
    public function loadPackages() {
        
        $db = MDB2::singleton(DB_DSN);
        
        $sql_structure = file_get_contents(dirname(__FILE__) . '../database-module_package-values.sql');
        $sql_arr = Install::splitSql($sql_structure);

        foreach($sql_arr as $sql) {
            if(empty($sql)) { continue; }
            $result = $this->db->exec($sql);
            if(PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
                exit;
            }
        }
    }
}

?>
