<?php
interface Observable
{
    function attach($observer);
}

interface Observer
{
    function update($code, $msg);
}

class Intraface_Log implements Observer
{
    private $db;
    private $table_name = 'log_table';
    private $table_definition = array(
        'id' => array(
            'type' => 'integer',
            'unsigned' => 1,
            'notnull' => 1,
            'default' => 0
            ),
        'logtime' => array(
            'type' => 'timestamp'
            ),
        'ident' => array(
            'type' => 'text',
            'length' => 16
            ),
        'priority' => array(
            'type' => 'integer',
            'notnull' => 1
            ),
        'message' => array(
            'type' => 'text',
            'length' => 200
        )
    );

    private $definition = array('primary' => true, 'fields' => array('id' => array()));

    function __construct()
    {
        $this->db = MDB2::singleton(DB_DSN);
        if (!$this->tableExists($this->table_name)) {
            $this->createTable();
        }
    }

    function tableExists($table)
    {
        $this->db->loadModule('Manager', null, true);
        $tables = $this->db->manager->listTables();
        if (PEAR::isError($tables)) {
            throw new Exception("Error in query: ".$tables->getUserInfo());
        }

        return in_array(strtolower($table), array_map('strtolower', $tables));
    }

    function createTable()
    {
        $this->db->loadModule('Manager');
        $result = $this->db->createTable($this->table_name, $this->table_definition);

        if (PEAR::isError($result)) {
            throw new Exception('create ' . $result->getMessage());
        }

        $result = $this->db->createConstraint($this->table_name, 'PRIMARY', $this->definition);
        if (PEAR::isError($result)) {
            throw new Exception('primary ' . $result->getMessage());
        }
    }

    function update($code, $msg)
    {
        // @todo should be replaced by the mdb2 driver, but at the moment it does not work directly
        $log = Log::singleton('sql', $this->table_name, $code, array('dsn' => DB_DSN));
        $log->log($msg);
        return true;
    }
}
