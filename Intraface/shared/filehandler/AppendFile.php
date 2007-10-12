<?php
require_once 'Intraface/DBQuery.php';

class AppendFile
{
    /**
     * @var integer
     */
    var $id;

    /**
     * @var object
     */
    var $object;

    /**
     * @var array
     */
    var $belong_to_types = array();

    /**
     * @var object
     */
    var $error;

    /**
     * @var object
     */
    var $dbquery;

    /**
     * Constructor
     *
     * @param object  $kernel       Kernel object
     * @param string  $belong_to    Which type the file belongs to
     * @param integer $belong_to_id The id this appended file belongs to
     * @param integer $id           @todo what is this used for
     *
     * @return void
     */
    public function __construct($kernel, $belong_to, $belong_to_id, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('AppendFile::__construct needs kernel', E_USER_ERROR);
            return false;
        }
        $this->kernel = $kernel;

        $shared_filehandler = $this->kernel->useShared('filehandler');
        $this->belong_to_types = $shared_filehandler->getSetting('file_append_belong_to_types');

        if(!in_array($belong_to, $this->belong_to_types)) {
            trigger_error("AppendFile->__construct unknown type", E_USER_ERROR);
        }
        $this->belong_to_key = array_search($belong_to, $this->belong_to_types);
        $this->belong_to_id = (int)$belong_to_id;

        $this->id = (int)$id;
        $this->error = new Error;

        /*
        if ($this->id > 0) {
            $this->load();
        }
        */
    }

    /**
     * Creates the dbquery so it can be used from everywhere
     *
     * @return void
     */
    public function createDBQuery()
    {
        $this->dbquery = new DBQuery($this->kernel, 'filehandler_append_file', 'filehandler_append_file.active = 1 AND filehandler_append_file.intranet_id='.$this->kernel->intranet->get('id').' AND filehandler_append_file.belong_to_key = '.$this->belong_to_key.' AND filehandler_append_file.belong_to_id = ' . $this->belong_to_id);
    }

    /**
     * Validates input for save
     *
     * @param array $var Values to validate
     *
     * @return boolean
     */
    function validate($var)
    {
        $filehandler = new Filehandler($this->kernel, (int)$var['file_handler_id']);
        if($filehandler->get('id') == 0) {
            $this->error->set('error in file');
            return false;
        } else {
            return true;
        }
    }

    /**
     * Saves the values
     *
     * @param array $var Values to save
     *
     * @return integer
     */
    function save($var)
    {
        $var = safeToDb($var);

        if (!$this->validate($var)) {
            return 0;
        }

        $db = new DB_Sql();
        $db->query("SELECT id FROM filehandler_append_file WHERE intranet_id = " . $this->kernel->intranet->get('id') . " AND belong_to_key = ".$this->belong_to_key." AND belong_to_id = ".$this->belong_to_id." AND file_handler_id = ".$var['file_handler_id']." AND active = 1");
        if ($db->nextRecord()) {
            // hvis filen allerede er tilknyttet lader vi som om alt gik godt, og vi siger go
            // dette skal naturligvis laves lidt anderledes, hvis vi skal have en description med
            return 1;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;

        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = " , date_created = NOW()";
        }

        $db->query($sql_type . " filehandler_append_file SET
            date_updated = NOW(),
            intranet_id = ".$this->kernel->intranet->get('id').",
            belong_to_key = ".$this->belong_to_key.",
            belong_to_id = ".$this->belong_to_id.",
            file_handler_id = ".$var['file_handler_id']."
            " . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
        return $this->id;
    }

    /**
     * Adds a file to this
     *
     * @param mixed $input Either an id or an array
     *
     * @return void
     */
    function addFile($input)
    {
        $input = safeToDb($input);

        if(is_numeric($input)) {
            $this->id = 0;
            $this->save(array('file_handler_id' => $input));
        } elseif(is_array($input)) {
            foreach($input AS $id) {

                $this->id = 0;
                $this->save(array('file_handler_id' => $id));
            }
        } else {
            trigger_error('AppendFile->addFile unknown type', E_USER_ERROR);
        }
    }

    /**
     * Deletes
     *
     * @return boolean
     */
    function delete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE filehandler_append_file SET active = 0 WHERE id = " . $this->id);
        return true;
    }

    /**
     * Undelete
     *
     * @return boolean
     */
    function undelete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE filehandler_append_file SET active = 1 WHERE id = " . $this->id);
        return true;
    }

    /**
     * Gets a list with appended files
     *
     * @return array
     */
    function getList()
    {
        if($this->dbquery->checkFilter('order_by') && $this->dbquery->getFilter('order_by') == 'name') {
            $this->dbquery->setJoin('INNER', 'file_handler', 'filehandler_append_file.file_handler_id = file_handler.id', 'file_handler.intranet_id = '.$this->kernel->intranet->get('id').' AND file_handler.active = 1');
            $this->dbquery->setSorting('file_handler.file_name');
        }
        else {
            $this->dbquery->setSorting('filehandler_append_file.id');
        }


        // $db = new DB_Sql;
        // $db->query("SELECT id, file_handler_id, description FROM filehandler_append_file WHERE active = 1 AND intranet_id=".$this->kernel->intranet->get('id')." AND belong_to_key = ".$this->belong_to_key." AND belong_to_id = " . $this->belong_to_id." ORDER BY id");
        $db = $this->dbquery->getRecordset('filehandler_append_file.id, filehandler_append_file.file_handler_id, filehandler_append_file.description');
        $i = 0;
        $files = array();
        while ($db->nextRecord()) {
            $files[$i]['id'] = $db->f('id');
            $files[$i]['file_handler_id'] = $db->f('file_handler_id');
            $files[$i]['description'] = $db->f('description');
            $i++;
        }
        return $files;
    }

}

?>