<?php

class ImageRandomizer
{
    
    /**
     * @var object $file_manager file handler
     */
    private $file_manager;
    
    /**
     * @var object $error
     */
    public $error;
    
    /**
     * @var array $file_list to find image from
     */
    private $file_list;
    
    /**
     * constructor
     * 
     * @param object $file_manager file handler
     * @param array $keywords array with keywords
     */
    public function __construct($file_manager, $keywords) {
        
        $this->file_manager = $file_manager;
        
        require_once 'Ilib/Error.php';
        $this->error = new Ilib_Error;
        
        $dbquery = $this->getDBQuery();
        
        require_once 'Intraface/shared/keyword/Keyword.php';
        if(!is_array($keywords)) {
            trigger_error('second parameter should be an array with keywords', E_USER_ERROR);
            return false;
        }
        
        $keyword_ids = array();
        foreach($keywords AS $keyword) {
            $keyword_object = new Keyword($this->file_manager);
            /**
             * @todo: This is not really good, but the only way to identify keyword on name!
             */
            $keyword_ids[] = $keyword_object->save(array('keyword' => $keyword));
        }
        
        $dbquery->setKeyword((array)$keyword_ids);
        
        
        require_once('Intraface/shared/filehandler/FileType.php');
        $filetype = new FileType();
        $types = $filetype->getList();
        $keys = array();
        foreach($types AS $key => $mime_type) {
            if($mime_type['image'] == 1) {
                $keys[] = $key;
            }
        }
        $dbquery->setCondition("file_handler.file_type_key IN (".implode(',', $keys).")");
        
        $this->file_list = array();
        $db = $dbquery->getRecordset("file_handler.id", "", false);
        while($db->nextRecord()) {
            $this->file_list[] = $db->f('id');
        }
        
        if(count($this->file_list) == 0) {
            trigger_error('No images found with the keywords: '.implode(', ', $keywords), E_USER_ERROR);
            exit;
        }
    }
    
    /**
     * returns dbquery
     * 
     * @return object dbquery
     */
    private function getDBQuery() 
    {
        require_once 'Ilib/DBQuery.php';
        $dbquery = new Ilib_DBQuery("file_handler", "file_handler.temporary = 0 AND file_handler.active = 1 AND file_handler.intranet_id = ".$this->file_manager->kernel->intranet->get('id'));
        $dbquery->useErrorObject($this->error);
        return $dbquery;
    }
    
    
    /**
     * return an file object with random image
     * 
     * @return object file_manager with random image loaded
     */
    public function getRandomImage() 
    {
        
        $key = rand(0, count($this->file_list)-1);
        return new FileHandler($this->file_manager->kernel, $this->file_list[$key]);
        
    }
  
}
?>
