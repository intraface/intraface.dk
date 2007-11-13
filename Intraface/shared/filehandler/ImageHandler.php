<?php
 /**
  * Image handler. Klarer håndtering af billeder.
  *
  * @package Intraface
  * @author: Sune
  * @version: 1.0
  *
  */

require_once 'Image/Transform.php';

class ImageHandler extends Standard
{
    /**
     * @var object
     */
    private $file_handler;

    /**
     * @var integer
     */
    private $image_library;
    
    /**
     * @var string tmp_file_name
     */
    private $tmp_file_name = NULL;

    /**
     * Constructor
     *
     * @param object $file_handler
     *
     * @return void
     */
    public function __construct($file_handler)
    {
        if(!is_object($file_handler)) {
            trigger_error("InstanceHandler kræver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (1)", E_USER_ERROR);
        }

        if(strtolower(get_class($file_handler)) == 'filehandler' || strtolower(get_class($file_handler)) == 'filemanager') {
            // HJÆLP MIG, jeg kan ikke vende denne if-sætning rigtigt.
            // Men her er det ok.
        } else {
            trigger_error("InstanceHandler kræver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (2)", E_USER_ERROR);
        }

        $this->file_handler = $file_handler;

        if(!defined('IMAGE_LIBRARY')) {
            define('IMAGE_LIBRARY', 'GD');
        }
        
        $this->image_library = IMAGE_LIBRARY;

        if($this->file_handler->get('is_image') != 1) {
            trigger_error("Filtypen " . $file_handler->get('mime_type') . " er ikke et billede, og kan derfor ikke manipuleres i ImageHandler", E_USER_ERROR);
        }

        $this->tempdir_path = $this->file_handler->getTemporaryDirectory();

        if(!is_dir($this->tempdir_path)) {
            if(!mkdir($this->tempdir_path)) {
                trigger_error('Kunne ikke oprette workdir '.$this->tempdir_path.'i ImageHandler->imageHandler', E_USER_ERROR);
            }
        }

    }

    /**
     * Resizes a picture
     *
     * @param float  $width  Width
     * @param float  $height Height
     * @param string $strict strict (the image fits the width and height strictly) or relative (the image fits inside the width and hight, but keeps ratio)
     *
     * @return string new file name
     */
    public function resize($width, $height = NULL, $strict = 'relative')
    {

        $image = Image_Transform::factory($this->image_library);
        if (PEAR::isError($image)) {
            trigger_error($image->getMessage() . $image->getUserInfo(), E_USER_ERROR);
            exit;
        }

        if($this->tmp_file_name != NULL && file_exists($this->tmp_file_name)) {
            $error = $image->load($this->tmp_file_name);
        }
        else {
            $error = $image->load($this->file_handler->get('file_path'));
        }
        

        $image->setOption('quality', 100);

        if($error !== true) {
            trigger_error("Kunne ikke åbne fil i ImageHandler->resize. ".$error->getMessage(), E_USER_ERROR);
            return false;
        }

        if(!in_array($strict, array('relative', 'strict'))) trigger_error("Den tredje parameter i ImageHandle->resize er ikke 'strict' eller 'relative'.", E_USER_ERROR);

        // die($image->img_x.':'.$image->img_y.':'.$width.':'.$height);
        if($strict == 'strict') {
            // same aspect ratio: doesn't mapper which way to scale
            if (($image->img_y/$image->img_x) < ($height/$width)) {
                $image->scaleByY($height);

                $offset_y = 0;
                $offset_x = floor(($image->new_x - $width)/2);
            } else {
                $image->scaleByX($width);
                $offset_y = floor(($image->new_y - $height)/2);
                $offset_x = 0;
            }

            // die($image->new_x.':'.$image->new_y.':'.$width.':'.$height.': '.$offset_x.': '.$offset_y);
            if($image->crop($width, $height, $offset_x, $offset_y) !== true){
                trigger_error("Der opstod en fejl under formatering (crop) af billedet i ImageHandler->resize", E_USER_ERROR);
                return false;
            }
        } else {

            if($image->fit($width, $height) !== true) {
                trigger_error("Der opstod en fejl under formatering (fit) af billedet i ImageHandler->resize", E_USER_ERROR);
                return false;
            }
        }

        $file_type = $this->file_handler->get('file_type');
        $new_filename = $this->tempdir_path.date('U').$this->file_handler->kernel->randomKey(10).'.'.$file_type['extension'];

        if($image->save($new_filename) !== true) {
            trigger_error("Kunne ikke gemme billedet i ImageHandler->resize", E_USER_ERROR);
            return false;
        }
        
        $this->tmp_file_name = $new_filename;
        return $new_filename;
    }



    /**
     * crop a picture
     *
     * @param float  $width  Width
     * @param float  $height Height
     * @param float $offset_x offset x
     * @param float $offset_y offset y
     *
     * @return string new file name
     */    
    function crop($width, $height, $offset_x = 0, $offset_y = 0) {
        $image = Image_Transform::factory($this->image_library);
        if (PEAR::isError($image)) {
            trigger_error($image->getMessage() . $image->getUserInfo(), E_USER_ERROR);
            exit;
        }

        if($this->tmp_file_name != NULL && file_exists($this->tmp_file_name)) {
            $error = $image->load($this->tmp_file_name);
        }
        else {
            $error = $image->load($this->file_handler->get('file_path'));
        }
        

        $image->setOption('quality', 100);

        if($error !== true) {
            trigger_error("Kunne ikke åbne fil i ImageHandler->resize. ".$error->getMessage(), E_USER_ERROR);
        }
        
        
        if($image->crop($width, $height, $offset_x, $offset_y) !== true){
            trigger_error("Der opstod en fejl under formatering (crop) af billedet i ImageHandler->crop", E_USER_ERROR);
            return false;
        }
        
        $file_type = $this->file_handler->get('file_type');

        $new_filename = $this->tempdir_path.date('U').$this->file_handler->kernel->randomKey(10).'.'.$file_type['extension'];

        if($image->save($new_filename) !== true) {
            trigger_error("Kunne ikke gemme billedet i ImageHandler->crop", E_USER_ERROR);
            return false;
        }
        
        $this->tmp_file_name = $new_filename;

        return $new_filename;
    }

    /**
     * @todo Get the relative size FOR WHAT?
     *
     * //100 //67
     *
     * @param integer $max_width  Maximal width
     * @param integer $max_height Maximal height
     */
    public function getRelativeSize($max_width, $max_height)
    {
        $width = $this->file_handler->get('width'); //1000
        $height = $this->file_handler->get('height'); //502

        if($width > $max_width) {
            $height = ($max_width/$width)*$height;
            $width = $max_width;
        }

        if($height > $max_height) {
            $width = ($max_height/$height)*$width;
            $height = $max_height;
        }

        return array('width' => round($width), 'height' => round($height));
    }

}
?>