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


class FileViewer
{

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


    public function __construct($filehandler, $instance = '')
    {

        if (!is_object($filehandler)) {
            throw new Exception('the first parameter needs to be filehandler in FileViewet->__construct');
        }

        $this->filehandler = $filehandler;
        $this->file_path = $filehandler->get('file_path');
        $this->file_name = $filehandler->get('file_name');

        $file_type = $filehandler->get('file_type');
        $this->mime_type = $file_type['mime_type'];

        $this->filehandler->createInstance();

        if (!empty($instance) && $filehandler->instance->checkType($instance) !== false) {
            $this->filehandler->createInstance($instance);
            $this->file_path = $filehandler->instance->get('file_path');
        }
    }

    public function needLogin()
    {
        return $this->filehandler->get('accessibility') != 'public';
    }

    function getMimeType()
    {
        return $this->mime_type;
    }

    function getFileName()
    {
        return $this->file_name;
    }

    function getLastModified()
    {
        return $last_modified = filemtime($this->file_path);
    }

    function fetch()
    {
        return file_get_contents($this->file_path);
    }

    public function out()
    {

        if (!file_exists($this->file_path)) {
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
