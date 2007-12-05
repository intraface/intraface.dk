<?php

/**
 * TemporaryFile class returns a possible path to a temporary file
 * 
 * @author sune jensen
 * @version 0.0.1
 * @category filehandler
 */

class TemporaryFile {
    
    /**
     * @var object $file_handler
     */
    private $filehandler;
    
    /**
     * @var string $file_name
     */
    private $file_name;
    
    /**
     * @var string $file_path
     */
    private $file_path ;
    
    /**
     * @var string $file_dir
     */
    private $file_dir;
    
    /**
     * Constructor
     * 
     * @param object $file_handler
     * @param string $file_name the name of the temporary file.
     */
    public function __construct($filehandler, $file_name = NULL)
    {
        if(!is_object($filehandler)) {
            trigger_error("TemporaryFile requires filehandler or filemanager", E_USER_ERROR);
        }

        $this->filehandler = $filehandler;
        $this->file_name = $file_name;
        $this->file_path = NULL;
        $this->file_dir = NULL;
        
        if($this->file_name != NULL) {
            $this->load();
        } 
    }
    
    /**
     * loads the temporary file, which means generates the path to the file.
     */
    private function load() 
    {
        if(empty($this->file_name)) {
            trigger_error('file_name needs to be set to load temporary file', E_USER_ERROR);
        }
        
        // We make sure to create the folders
        if(!is_dir($this->filehandler->upload_path)) {
            if(!mkdir($this->filehandler->upload_path)) {
                trigger_error('Unable to create upload dir "'.$this->filehandler->upload_path.'"', E_USER_ERROR);
                exit;
            }
        }

        if(!is_dir($this->filehandler->tempdir_path)) {
            if(!mkdir($this->filehandler->tempdir_path)) {
                trigger_error('Unable to create temp dir "'.$this->filehandler->tempdir_path.'"', E_USER_ERROR);
                exit;
            }
        }
        
        $i = 0;
        do {
            $unique_name = uniqid();
            $i++;
            if($i == 50) {
                trigger_error('Error generating a unique name', E_USER_ERROR);
                exit;
            }
        }
        while (is_dir($this->filehandler->tempdir_path.$unique_name));
        
        if(!mkdir($this->filehandler->tempdir_path.$unique_name)) {
            trigger_error('Unable to create temporary dir "'.$this->filehandler->tempdir_path.$unique_name.'"', E_USER_ERROR);
            exit;
        }
        
        $this->file_name = $this->parseFileName($this->file_name);
        $this->file_dir = $this->filehandler->tempdir_path.$unique_name.DIRECTORY_SEPARATOR;
        $this->file_path = $this->file_dir.$this->file_name;
    }
    
    /**
     * Parses the filename and removes unwanted characters and controls the length
     * 
     * @param string $file_name the name to be parsed
     * @return string parsed file name
     */
    private function parseFileName($file_name) {
        
        
        $file_name = str_replace(' ', '_', $file_name);
        $file_name = str_replace('/', '_', $file_name);
        $file_name = str_replace('\\', '_', $file_name);
        $file_name = str_replace('#', '', $file_name);
        
        if(strlen($file_name) > 50) {
            $extension = strrchr($file_name, '.');
            if($extension !== false && (strlen($extension) == 4 || strlen($extension) == 5)) {
                $file_name = substr($file_name, 0, 50-strlen($extension)).$extension;
            }
            else {
                $file_name = substr($file_name, 0, 50);
            }   
        }
        
        return $file_name;
    }
    
    /**
     * Sets the filename, if it is not already done in the constructor.
     * 
     * @param string $file_name the name of the file.
     * @return string file_path;
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
        $this->load();
        return $this->getFilePath();
    }
    
    /**
     * Returns the file path
     * 
     * @return string file_path
     */
    public function getFilePath() 
    {
        return $this->file_path;
    }
    
    /**
     * Returns the file name after it has been parsed.
     * 
     * @return string parsed file name
     */
    function getFileName() 
    {
        return $this->file_name;
    }
    
    /**
     * Returns the file name after it has been parsed.
     * 
     * @return string parsed file name
     */
    function getFileDir() 
    {
        return $this->file_dir;
    }   
}
?>
