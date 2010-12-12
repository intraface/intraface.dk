<?php
class ImageRandomizer
{
    /**
     * @var object $file_manager file handler
     */
    protected $file_manager;

    /**
     * @var object $error
     */
    public $error;

    /**
     * @var array $file_list to find image from
     */
    protected $file_list;

    protected $dbquery;

    /**
     * Constructor
     *
     * @param object $file_manager file handler
     * @param array  $keywords     array with keywords
     *
     * @return void
     */
    public function __construct($file_manager, $keywords)
    {
        $this->file_manager = $file_manager;
        $this->error = new Ilib_Error;

        $this->dbquery = new Ilib_DBQuery("file_handler", "file_handler.temporary = 0 AND file_handler.active = 1 AND file_handler.intranet_id = ".$this->file_manager->getKernel()->intranet->get("id"));

        $keyword_ids = array();

        require_once 'Intraface/shared/keyword/Keyword.php';
        foreach ($keywords AS $keyword) {
            $keyword_object = new Keyword($this->file_manager);
            // @todo: This is not really good, but the only way to identify keyword on name!
            $keyword_ids[] = $keyword_object->save(array('keyword' => $keyword));
        }
        $this->dbquery->setKeyword($keyword_ids);

        require_once 'Intraface/modules/filemanager/FileType.php';
        $filetype = new FileType();
        $types = $filetype->getList();
        $keys = array();
        foreach ($types AS $key => $mime_type) {
            if ($mime_type['image'] == 1) {
                $keys[] = $key;
            }
        }
        $this->dbquery->setCondition("file_handler.file_type_key IN (".implode(',', $keys).")");

        $this->file_list = array();

        $db = $this->dbquery->getRecordset("file_handler.id", "");

        while ($db->nextRecord()) {
            $this->file_list[] = $db->f('id');
        }

        if (count($this->file_list) == 0) {
            throw new Exception('No images found with the keywords: '.implode(', ', $keywords));
        }
    }

    /**
     * returns dbquery
     *
     * @return object dbquery
     */
    protected function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }

        $dbquery = new Ilib_DBQuery("file_handler", "file_handler.temporary = 0 AND file_handler.active = 1 AND file_handler.intranet_id = ".$this->file_manager->getKernel()->intranet->get('id'));
        $dbquery->useErrorObject($this->error);
        return ($this->dbquery = $dbquery);
    }

    /**
     * return an file object with random image
     *
     * @return object file_manager with random image loaded
     */
    public function getRandomImage()
    {
        require_once 'Intraface/modules/filemanager/FileHandler.php';
        $key = rand(0, count($this->file_list)-1);
        $filehandler = new FileHandler($this->file_manager->getKernel(), $this->file_list[$key]);
        return $filehandler;
    }
}
