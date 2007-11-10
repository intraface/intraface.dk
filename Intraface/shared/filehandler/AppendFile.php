<?php
/**
 * @package Intraface
 */
require_once 'Intraface/DBQuery.php';

class AppendFile
{
    /**
     * @var array
     */
    private $belong_to_types = array();

    /**
     * @var object
     */
    public $error;

    /**
     * @var object
     */
    public $dbquery;

    /**
     * Constructor
     *
     * @param object  $kernel       Kernel object
     * @param string  $belong_to    Which type the file belongs to
     * @param integer $belong_to_id The id this appended file belongs to
     *
     * @return void
     */
    public function __construct($kernel, $belong_to, $belong_to_id)
    {
        if (!is_object($kernel)) {
            trigger_error('AppendFile::__construct needs kernel', E_USER_ERROR);
            return false;
        }
        $this->registerBelongTo(0, '_invalid_');
        $this->registerBelongTo(1, 'cms_element_gallery');
        $this->registerBelongTo(2, 'procurement_procurement');
        $this->registerBelongTo(3, 'product');
        $this->registerBelongTo(4, 'cms_element_filelist');

        if(!in_array($belong_to, $this->belong_to_types)) {
            trigger_error("AppendFile->__construct unknown type", E_USER_ERROR);
        }

        $this->belong_to_key = $this->getBelongToKey($belong_to);
        $this->belong_to_id = (int)$belong_to_id;

        $this->kernel = $kernel;
        $this->error = new Error;

    }

    /**
     * Register the belong to
     *
     * @param integer $key        The key to apply to the belong to
     * @param integer $identifier The way to know the identifier
     *
     * @return void
     */
    protected function registerBelongTo($key, $identifier)
    {
        $this->belong_to_types[$key] = $identifier;
    }

    /**
     * Gets the belon to
     *
     * @param integer $key        The key to apply to the belong to
     *
     * @return string
     */
    protected function getBelongTo($key)
    {
        return $this->belong_to_types[$key];
    }

    /**
     * Register the belong to key
     *
     * @param integer $identifier The way to know the identifier
     *
     * @return integer
     */
    protected function getBelongToKey($identifier)
    {
        return array_search($identifier, $this->belong_to_types);
    }

    /**
     * Creates the dbquery so it can be used from everywhere
     *
     * @return void
     */
    public function createDBQuery()
    {
        $this->dbquery = new Ilib_DBQuery('filehandler_append_file', 'filehandler_append_file.active = 1 AND filehandler_append_file.intranet_id='.$this->kernel->intranet->get('id').' AND filehandler_append_file.belong_to_key = '.$this->belong_to_key.' AND filehandler_append_file.belong_to_id = ' . $this->belong_to_id);
        $this->dbquery->createStore($this->kernel->getSessionId(), 'intranet_id = '.intval($this->kernel->intranet->get('id')));
    }

    /**
     * Checks whether the file has already been appended
     *
     * @param integer $file_id The file id to check
     *
     * @return mixed Either integer if, or false if not
     */
    protected function fileExists($file_id)
    {
        $db = new DB_Sql();
        $db->query("SELECT id FROM filehandler_append_file
            WHERE intranet_id = " . $this->kernel->intranet->get('id') . "
                AND belong_to_key = ".$this->belong_to_key."
                AND belong_to_id = ".$this->belong_to_id."
                AND file_handler_id = ".$file_id."
                AND active = 1");
        if ($db->nextRecord()) {
            return $db->f('id');
        } else {
            return false;
        }
    }

    /**
     * Adds a file to this
     *
     * @param object $file A filehandler file
     *
     * @return integer
     */
    public function addFile($file)
    {
        $file_id = $file->getId();

        if ($id = $this->fileExists($file_id)) {
            return $id;
        }

        $db = new DB_Sql();
        $db->query("INSERT INTO filehandler_append_file SET
            date_updated = NOW(),
            intranet_id = ".$this->kernel->intranet->get('id').",
            belong_to_key = ".$this->belong_to_key.",
            belong_to_id = ".$this->belong_to_id.",
            file_handler_id = ".$file_id.",
            date_created = NOW()");

        return $db->insertedId();
    }

    /**
     * Adds an array with files
     *
     * @param array $files An array with files
     *
     * @return boolean
     */
    function addFiles($files = array())
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
        return true;
    }

    /**
     * Deletes
     *
     * @param integer $id The appended file id to delete
     *
     * @return boolean
     */
    public function delete($id)
    {
        $db = new DB_Sql;
        $db->query("UPDATE filehandler_append_file
            SET active = 0
            WHERE id = " . $id);
        return true;
    }

    /**
     * Undelete
     *
     * @return boolean
     */
    public function undelete($id)
    {
        $db = new DB_Sql;
        $db->query("UPDATE filehandler_append_file
            SET active = 1
            WHERE id = " . $id);
        return true;
    }

    /**
     * Gets a list with appended files
     *
     * @return array
     */
    public function getList()
    {
        if($this->dbquery->checkFilter('order_by') && $this->dbquery->getFilter('order_by') == 'name') {
            $this->dbquery->setJoin('INNER', 'file_handler', 'filehandler_append_file.file_handler_id = file_handler.id', 'file_handler.intranet_id = '.$this->kernel->intranet->get('id').' AND file_handler.active = 1');
            $this->dbquery->setSorting('file_handler.file_name');
        } else {
            $this->dbquery->setSorting('filehandler_append_file.id');
        }

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