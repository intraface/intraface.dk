<?php
/**
 * FileViewer
 *
 * @todo - how to get the filehandler coming into the class
 * so I can fake it - and how to put in the authentication when
 * it is only needed sometimes?
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'Intraface/Weblogin.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';


class FileViewer {
    
    /**
     * @var string file_name
     */
    private $file_name;
    
    /**
     * @var string mime_type
     */
    private $mime_type;
    
    /**
     * @var string file_path
     */
    private $file_path;
    
    /**
     * @var object filehandler
     */
    private $filehandler;
    
    
    public function __construct($filehandler, $instance) {
        
        if(!is_object($filehandler)) {
            trigger_error('the first parameter needs to be filehandler in FileViewet->__construct', E_USER_ERROR);
            exit;
        }
        
        $this->filehandler = $filehandler;
        $this->file_path = $filehandler->get('file_path');
        $this->file_name = $filehandler->get('file_name');
        $_file_type = array();
        // $file_type is filled by the include_file
        require(PATH_INCLUDE_CONFIG.'setting_file_type.php');
        $this->mime_type = $_file_type[$filehandler->get('file_type_key')]['mime_type'];
        
        $this->filehandler->createInstance();
        
        if(!empty($instance) && $filehandler->instance->checkType($instance) !== false) {
            $this->filehandler->createInstance($instance);
            $this->file_path = $filehandler->instance->get('file_path');
        }
    }
    
    public function needLogin() {
        return $this->filehandler->get('accessibility') != 'public';
    }
    
    
    public function out() {
        
        if(!file_exists($this->file_path)) {
            return 'invalid file';
        }
        
        $last_modified = filemtime($this->file_path);
        
        header('Content-Type: '.$this->mime_type);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified).' GMT');
        header('Cache-Control:');
        header('Content-Disposition: inline; filename='.$this->file_name);
        header('Pragma:');
        return readfile($this->file_path);
    }
    
}




class _old_FileViewer
{

    /**
     * @var object
     */
    private $filehandler;

    /**
     * @var string
     */
    public $public_key;

    /**
     * @var string
     */
    public $file_key;

    /**
     * @var string
     */
    public $file_type;

    function __construct() {
    }

    /**
     * Parses a querystring and sets class variables
     *
     * @param string $querystring The querystring to parse
     *
     * @return void
     */
    public function parseQueryString($querystring)
    {
        $query_parts = explode('/', $querystring);
        $this->public_key = addslashes($query_parts[1]);
        $this->file_key = addslashes($query_parts[2]);
        $this->file_type = addslashes($query_parts[3]);
    }

    /**
     * Fetches the file
     *
     * @todo putte skabelsen af en filehandler i en gateway, hvor den finder
     *       fra querystring automatisk
     *
     * @param string $querystring
     *
     * @return
     */
    function fetch($querystring)
    {
        $this->parseQueryString($querystring);

        $weblogin = new Weblogin();
        if (!$intranet_id = $weblogin->auth('public', $this->public_key)) {
            die('FEJL I LÆSNING AF BILLEDE (0)');
        }
        if($intranet_id == false) {
            trigger_error("FEJL I LÆSNING AF BILLEDE (1)", E_USER_ERROR);
        }

        $kernel = new Kernel;
        $kernel->intranet = new Intranet($intranet_id);
        $filehandler_shared = $kernel->useShared('filehandler');

        $filehandler = FileHandler::factory($kernel, $this->file_key);
        if(!is_object($filehandler)) {
            trigger_error("FEJL I LÆSNING AF BILLEDE (2)", E_USER_ERROR);
        }

        switch($filehandler->get('accessibility')) {
            case 'personal':
                // Not implemented - continue to next
            case 'intranet':
                // You have to be logged in to access this file
                session_start();
                $auth = new Auth(session_id());

                if (!$user_id = $auth->isLoggedIn()) {
                    die("FEJL I LÆSNING AF BILLEDE (4)");
                }

                $user = new User($user_id);
                $intranet = new Intranet($user->getActiveIntranetId());

                if($intranet->get('id') != $intranet_id) {
                    die("FEJL I LÆSNING AF BILLEDE (4)");
                }

                break;
            case 'public':
                // public alle må se den
                break;
            default:
                // Dette er en ugyldig type
                trigger_error("FEJL I LÆSNING AF BILLEDE (5)", E_USER_ERROR);
            break;
        }

        $file_id = $filehandler->get('id');
        $file_name = $filehandler->get('file_name');
        $mime_type = $_file_type[$filehandler->get('file_type_key')]['mime_type'];
        $file_path = $filehandler->get('file_path');

        $filehandler_shared->includeFile('InstanceHandler.php');
        $instancehandler = new InstanceHandler($filehandler);

        if($instancehandler->_checkType($this->file_type) !== false) {
            $filehandler->createInstance($this->file_type);
            $file_path = $filehandler->instance->get('file_path');
        }

        $last_modified = filemtime($file_path);

        header('Content-Type: '.$mime_type);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified).' GMT');
        header('Cache-Control:');
        header('Content-Disposition: inline; filename='.$file_name);
        header('Pragma:');

        readfile($file_path);
        exit;
    }
}
?>