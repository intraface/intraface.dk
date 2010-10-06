<?php
 /**
  * Image handler. Klarer h�ndtering af billeder.
  *
  * @package Intraface
  * @author: Sune
  * @version: 1.0
  *
  */

class ImageHandler extends Intraface_Standard
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
        if (!is_object($file_handler)) {
            throw new Exception("InstanceHandler kræver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (1)");
        }

        if (strtolower(get_class($file_handler)) == 'filehandler' || strtolower(get_class($file_handler)) == 'filemanager') {
            // TODO: HJ�LP MIG, jeg kan ikke vende denne if-s�tning rigtigt.
            // Men her er det ok.
        } else {
            throw new Exception("InstanceHandler kræver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (2)");
        }

        $this->file_handler = $file_handler;

        if (!defined('IMAGE_LIBRARY')) {
            define('IMAGE_LIBRARY', 'GD');
        }

        $this->image_library = IMAGE_LIBRARY;

        if ($this->file_handler->get('is_image') != 1) {
            throw new Exception("Filtypen " . $file_handler->get('mime_type') . " er ikke et billede, og kan derfor ikke manipuleres i ImageHandler");
        }

        $this->tempdir_path = $this->file_handler->getTemporaryDirectory();

        if (!is_dir($this->tempdir_path)) {
            if (!mkdir($this->tempdir_path, 0755)) {
                throw new Exception('Kunne ikke oprette workdir '.$this->tempdir_path.'i ImageHandler->imageHandler');
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
    public function resize($width, $height, $strict = 'relative')
    {

        $image = Image_Transform::factory($this->image_library);
        if (PEAR::isError($image)) {
            throw new Exception($image->getMessage() . $image->getUserInfo());
        }

        if ($this->tmp_file_name != NULL && file_exists($this->tmp_file_name)) {
            $error = $image->load($this->tmp_file_name);
        }
        else {
            $error = $image->load($this->file_handler->get('file_path'));
        }


        $image->setOption('quality', 95);

        if ($error !== true) {
            throw new Exception("Kunne ikke åbne fil i ImageHandler->resize. ".$error->getMessage());
        }

        if (!in_array($strict, array('relative', 'strict'))) {
            throw new Exception("Den tredje parameter i ImageHandle->resize er ikke 'strict' eller 'relative'.");
        }

        // die($image->img_x.':'.$image->img_y.':'.$width.':'.$height);
        if ($strict == 'strict') {
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
            if ($image->crop($width, $height, $offset_x, $offset_y) !== true){
                throw new Exception("Der opstod en fejl under formatering (crop) af billedet i ImageHandler->resize");
            }
        } else {

            if ($image->fit($width, $height) !== true) {
                throw new Exception("Der opstod en fejl under formatering (fit) af billedet i ImageHandler->resize");
            }
        }

        $file_type = $this->file_handler->get('file_type');
        $new_file = $this->file_handler->createTemporaryFile($this->file_handler->get('server_file_name'));
        // $new_filename = $this->tempdir_path.date('U').$this->file_handler->kernel->randomKey(10).'.'.$file_type['extension'];

        if ($image->save($new_file->getFilePath()) !== true) {
            throw new Exception("Kunne ikke gemme billedet i ImageHandler->resize");
        }

        $this->tmp_file_name = $new_file->getFilePath();
        return $new_file->getFilePath();
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
    function crop($width, $height, $offset_x = 0, $offset_y = 0)
    {
        $image = Image_Transform::factory($this->image_library);
        if (PEAR::isError($image)) {
            throw new Exception($image->getMessage() . $image->getUserInfo());
        }

        if ($this->tmp_file_name != NULL && file_exists($this->tmp_file_name)) {
            $error = $image->load($this->tmp_file_name);
        } else {
            $error = $image->load($this->file_handler->get('file_path'));
        }

        $image->setOption('quality', 95);

        if ($error !== true) {
            throw new Exception("Kunne ikke åbne fil i ImageHandler->resize. ".$error->getMessage());
        }

        $result = $image->crop($width, $height, $offset_x, $offset_y);

        if (PEAR::isError($result)) {
            throw new Exception("Der opstod en fejl under formatering (crop) af billedet i ImageHandler->crop: " . $result->getMessage());
        }

        $file_type = $this->file_handler->get('file_type');

        $new_file = $this->file_handler->createTemporaryFile($this->file_handler->get('server_file_name'));
        // $new_filename = $this->tempdir_path.date('U').$this->file_handler->kernel->randomKey(10).'.'.$file_type['extension'];

        if ($image->save($new_file->getFilePath()) !== true) {
            throw new Exception("Kunne ikke gemme billedet i ImageHandler->crop");
        }

        $this->tmp_file_name = $new_file->getFilePath();
        return $new_file->getFilePath();
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

        if ($width > $max_width) {
            $height = ($max_width/$width)*$height;
            $width = $max_width;
        }

        if ($height > $max_height) {
            $width = ($max_height/$height)*$width;
            $height = $max_height;
        }

        return array('width' => round($width), 'height' => round($height));
    }

}
?>