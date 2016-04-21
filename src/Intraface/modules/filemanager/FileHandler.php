<?php
/**
 * FileHandler
 *
 * Har grundlæggende kontrol over filer der uploades til systemet.
 * FileHandler i include/3party omdøbes til fileModifier
 * Filehandler benytter FileUpload og FileModifier.
 *
 * FileManager er modullet hvor man også kan se browse og ændre filerne.
 * Dette vil benytte FileHandler.
 *
 * @package Intraface
 * @author  Sune Jensen
 * @since   1.2
 */
class FileHandler extends Intraface_Standard
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var object
     */
    public $kernel;

    /**
     * @var object
     */
    public $error;

    /**
     * @var string
     */
    public $upload_path;

    /**
     * @var string
     */
    public $tempdir_path;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var array
     */
    protected $file_types = array();

    /**
     * @var array
     */
    protected $accessibility_types = array(
        0 => '_invalid_',
        1 => 'user',
        2 => 'intranet',
        3 => 'public');

    /**
     * @var array
     */
    private $status = array(
        0 => 'visible',
        1 => 'temporary',
        2 => 'hidden');

    /**
     * @var object upload conatians upload handler
     */
    public $upload;

    /**
     * @var object instance contains the instance handler.
     */
    public $instance;

    /**
     * @var object image contains the image handler
     */
    public $image;

    /**
     * @todo der er muligt, at der kun skal være en getList i filemanager,
     *       men så skal vi have cms til at have filemanager som dependent. Foreløbig
     *       har jeg lavet keywordsøgning i denne LO
     * @var object
     */
    public $dbquery;

    /**
     * @var string
     */
    private $file_viewer;

    /**
     * @var string
     */
    private $www_path;

    /**
     * Constructor
     *
     * @param object  $kernel  Kernel object
     * @param integer $file_id The file id
     *
     * @return void
     */
    public function __construct($kernel, $file_id = 0)
    {
        $this->kernel = $kernel;
        $this->id = (int)$file_id;
        $this->error = new Ilib_Error;

        $this->upload_path = PATH_UPLOAD . $this->kernel->intranet->get('id') . '/';
        $this->tempdir_path = $this->upload_path.PATH_UPLOAD_TEMPORARY;
        $this->file_viewer = FILE_VIEWER;
        $this->www_path = PATH_WWW;

        if (!is_dir($this->upload_path)) {
            if (!mkdir($this->upload_path, 0755)) {
                throw new Exception("Unable to create folder '".$this->upload_path."'");
            }
        }

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Used by Keyword
     *
     * @see Keyword
     *
     * @return string
     */
    function identify()
    {
        return 'file_handler';
    }

    /**
     * desctructor
     */
    public function __destruct()
    {
        unset($this->kernel);
        if (isset($this->instance)) {
            $this->instance->__destruct();
        }
        unset($this->instance);
        unset($this->image);
    }

    /**
     * Returns the access key for the file
     *
     * @return string
     */
    public function getAccessKey()
    {
        return $this->get('access_key');
    }

    /**
     * Returns the upload path
     *
     * @return string
     */
    public function getUploadPath()
    {
        return $this->upload_path;
    }

    /**
     * Returns the id for the file
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the temporary directory path
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->tempdir_path;
    }

    /**
     * Creates a filehandler
     *
     * @param object $kernel     Kernel object
     * @param string $access_key The accesskey
     *
     * @return object
     */
    public function factory($kernel, $access_key)
    {
        $access_key = safeToDb($access_key);

        $db = new DB_Sql;
        $db->query("SELECT id FROM file_handler WHERE intranet_id = ".$kernel->intranet->get('id')." AND active = 1 AND access_key = '".$access_key."'");
        if (!$db->nextRecord()) {
            return false;
        }
        return new FileHandler($kernel, $db->f('id'));
    }

    /**
     * Loads the file
     *
     * @return integer
     */
    public function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, date_created, width, height, date_changed, description, file_name, server_file_name, file_size, access_key, accessibility_key, file_type_key, DATE_FORMAT(date_created, '%d-%m-%Y') AS dk_date_created, DATE_FORMAT(date_changed, '%d-%m-%Y') AS dk_date_changed FROM file_handler WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return 0;
        }

        $this->value['id'] = $db->f('id');
        $this->value['date_created'] = $db->f('date_created');
        $this->value['dk_date_created'] = $db->f('dk_date_created');
        $this->value['date_changed'] = $db->f('date_changed');
        $this->value['dk_date_changed'] = $db->f('dk_date_changed');
        $this->value['description'] = $db->f('description');
        if (empty($this->value['description'])) {
            $this->value['description'] = $db->f('file_name');
        }
        $this->value['name'] = $db->f('file_name'); // bruges af keywords
        $this->value['file_name'] = $db->f('file_name');
        $this->value['server_file_name'] = $db->f('server_file_name');
        $this->value['original_server_file_name'] = $this->value['server_file_name'];
        $this->value['file_size'] = $db->f('file_size');
        $this->value['access_key'] = $db->f('access_key');

        $this->value['accessibility'] = $this->accessibility_types[$db->f('accessibility_key')];

        if ($this->value['file_size'] >= 1000000) {
            $this->value['dk_file_size'] = number_format(($this->value['file_size']/1000000), 2, ",", ".")." Mb";
        } elseif ($this->value['file_size'] >= 1000) {
            $this->value['dk_file_size'] = number_format(($this->value['file_size']/1000), 2, ",", ".")." Kb";
        } else {
            $this->value['dk_file_size'] = number_format($this->value['file_size'], 2, ",", ".")." byte";
        }

        $this->value['file_type_key'] = (int)$db->f('file_type_key');
        $this->loadMimeTypes();
        $this->value['file_type'] = $this->_getMimeType((int)$db->f('file_type_key'));
        $this->value['is_image'] = $this->file_types[$this->get('file_type_key')]['image'];
        $this->value['file_path'] = $this->upload_path . $db->f('server_file_name');

        if (file_exists($this->get('file_path'))) {
            $this->value['last_modified'] = filemtime($this->get('file_path'));
        } else {
            $this->value['last_modified'] = 'Filen findes ikke';
        }

        $this->value['file_uri'] = $this->file_viewer.'?/'.$this->kernel->intranet->get('public_key').'/'.$this->get('access_key').'/'.urlencode($this->get('file_name'));
        // used for pdf's
        $this->value['file_uri_pdf'] = $this->upload_path.$this->value['server_file_name'];

        if ($this->value['is_image'] == 1) {
            $this->value['icon_uri'] = $this->file_viewer.'?/'.$this->kernel->intranet->get('public_key').'/'.$db->f('access_key').'/system-square/'.urlencode($db->f('file_name'));
            $this->value['icon_width'] = 75;
            $this->value['icon_height'] = 75;
        } else {
            $this->value['icon_uri'] = $this->www_path.'images/mimetypes/'.$this->value['file_type']['icon'];
            $this->value['icon_width'] = 75;
            $this->value['icon_height'] = 75;
        }

        if ($this->value['is_image'] == 1) {
            if ($db->f('width') == null) {
                $imagesize = getimagesize($this->get('file_path'));
                $this->value['width'] = $imagesize[0]; // imagesx($this->get('file_uri'));
                $db2 = new DB_sql;
                $db2->query("UPDATE file_handler SET width = ".(int)$this->value['width']." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
            } else {
                $this->value['width'] = $db->f('width');
            }

            if ($db->f('height') == null) {
                $imagesize = getimagesize($this->get('file_path'));
                $this->value['height'] = $imagesize[1]; //imagesy($this->get('file_uri'));
                $db2 = new DB_sql;
                $db2->query("UPDATE file_handler SET height = ".(int)$this->value['height']." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
            } else {
                $this->value['height'] = $db->f('height');
            }
        } else {
            $this->value['width'] = '';
            $this->value['height'] = '';
        }

        return $this->id;
    }

    /**
     * Creates the upload object so it can be used in the class
     *
     * @todo is this used at all?
     *
     * @return void
     */
    public function createUpload()
    {
        require_once 'Intraface/modules/filemanager/UploadHandler.php';
        $this->upload = new UploadHandler($this);
    }

    /**
     * Creates the the instance handler so it can be used directly from the filehandler class
     *
     * @return void
     */
    public function createInstance($type = "", $param = array())
    {
        require_once 'Intraface/modules/filemanager/InstanceHandler.php';
        if ($type == "") {
            $this->instance = new InstanceHandler($this);
        } else {
            $this->instance = InstanceHandler::factory($this, $type, $param);
        }
    }

    function getInstance($type = '', $param = array())
    {
        $this->createInstance($type, $param);
        return $this->instance;
    }

    /**
     * Creates the the image handler so it can be used directly from the filehandler class
     *
     * @todo is this used?
     *
     * @return void
     */
    public function createImage()
    {
        require_once 'Intraface/modules/filemanager/ImageHandler.php';
        $this->image = new ImageHandler($this);
    }

    /**
     * creates and temporary file handler
     *
     * @param string optional file name
     * @return object temporary file
     */
    public function createTemporaryFile($file_name = null)
    {
        require_once 'Intraface/modules/filemanager/TemporaryFile.php';
        return new TemporaryFile($this, $file_name);
    }

    /**
     * Delete
     *
     * Sletter fil: Sætter active = 0 og sætter _deleted_ foran filen.
     *
     * Her bør sikkert være et tjek på om filen bruges nogen steder i systemet.
     * Hvis den bruges skal man måske have at vide hvor?
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->id == 0) {
            return false;
        }

        $db = new DB_Sql;

        if ($this->get('server_file_name') != '' && file_exists($this->get('file_path'))) {
            if (!rename($this->get('file_path'), $this->upload_path.'_deleted_'.$this->get('server_file_name'))) {
                throw new Exception("Kunne ikke omdøbe filen i FileHandler->delete()");
            }
        }

        $db->query("UPDATE file_handler SET active = 0 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        return true;
    }

    /**
     * Undeletes a file
     *
     * @return boolean
     */
    public function undelete()
    {
        if ($this->id == 0) {
            return false;
        }

        $db = new DB_Sql;
        $deleted_file_name = $this->upload_path . '_deleted_' . $this->get('server_file_name');
        if (file_exists($deleted_file_name)) {
            if (!rename($deleted_file_name, $this->upload_path.$this->get('server_file_name'))) {
                throw new Exception("Kunne ikke omdøbe filen i FileHandler->undelete()");
            }
        }

        $db->query("UPDATE file_handler SET active = 1 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        return true;
    }

    /**
     * Benyttes til at sætte en uploadet fil ind i systemet
     *
     * @todo should be called something else
     *
     * @param string $file      stien til filen @todo what exactly is this?
     * @param string $file_name det originale filnavn, hvis ikke sat, tages der efter det nuværende navn
     * @param string $status    @todo hvad er det
     * @param string $mime_type @todo hvad er det
     *
     * @return integer
     */
    public function save($file, $file_name = '', $status = 'visible', $mime_type = null)
    {
        if (!is_file($file)) {
            $this->error->set("error in input - not valid file");
            return false;
        }

        $file = realpath($file);

        if (!in_array($status, $this->status)) {
            throw new Exception("Trejde parameter '".$status."' er ikke gyldig i Filehandler->save");
        }

        $db = new DB_Sql;

        if ($file_name == '') {
            $file_name = substr(strrchr($file, '/'), 1);
        } else {
            $file_name = safeToDb($file_name);
        }

        $random_key_generator = $this->getRandomKeyGenerator();

        // Vi sikre os at ingen andre har den n�gle
        $i = 0;
        do {
            $access_key = $random_key_generator->generate(50);

            if ($i > 50 || $access_key == '') {
                throw new Exception("Fejl under generering af access_key i FileHandler->save");
            }
            $i++;
            $db->query("SELECT id FROM file_handler WHERE access_key = '".$access_key."'");
        } while ($db->nextRecord());

        $file_size = filesize($file);

        // if mime type is not set as the parameter, we try to determine the mimetype
        if ($mime_type === null) {
            // $mime_type = mime_content_type($file);
            $mime_type = MIME_Type::autoDetect($file);
            if (PEAR::isError($mime_type)) {
                throw new Exception("Error in MIME_Type::autoDetect in Filehandler->save() ".$mime_type->getMessage());
            }
        }

        // we load our own mimetypes which have more information.
        // @todo we need to update to the new FileInfo extension
        //       as soon as possible, as autodetect will not work
        //       on windows, and that is pretty annoying in tests.
        $mime_type = $this->_getMimeType($mime_type, 'mime_type');
        if ($mime_type === false) {
            $this->error->set('error in filetype');
            return false;
        }

        if ($mime_type['image']) {
            $imagesize = getimagesize($file);
            $width = $imagesize[0]; // imagesx($file);
            $height = $imagesize[1]; // imagesy($file);
        } else {
            $width = "NULL";
            $height = "NULL";
        }

        $accessibility_key = array_search('intranet', $this->accessibility_types);

        $sql = "date_changed = NOW(),
            access_key = '".$access_key."',
            file_name = '".$file_name."',
            file_size = '".(int)$file_size."',
            file_type_key = ".$mime_type['key'].",
            accessibility_key = ".$accessibility_key.",
            width = ".$width.",
            height = ".$height.",
            temporary = ".array_search($status, $this->status)."";

        if ($this->id != 0) {
            $db->query("UPDATE file_handler SET ".$sql." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
            $id = $this->id;

            // deleting the old file
            if (!rename($this->get('file_path'), $this->upload_path.'_deleted_'.$this->get('server_file_name'))) {
                throw new Exception("Was not able to rename file ".$this->get('file_path')." in Filehandler->save()");
            }
            $this->createInstance();
            $this->instance->deleteAll();
        } else {
            $db->query("INSERT INTO file_handler SET ".$sql.", date_created = NOW(), intranet_id = ".$this->kernel->intranet->get('id').", user_id = ".$this->kernel->user->get('id'));
            $id = $db->insertedId();
        }

        $server_file_name = $id.'.'.$mime_type['extension'];

        if (!is_file($file)) {
            throw new Exception("Filen vi vil flytte er ikke en gyldig fil i filehandler->save");
        }

        if (!rename($file, $this->upload_path.$server_file_name)) {
            $this->delete();
            throw new Exception("Unable to move file '".$file."' to '".$this->upload_path.$server_file_name."' in Filehandler->save");
        }

        if (!chmod($this->upload_path.$server_file_name, 0644)) {
            // please do not stop executing here
        }

        $db->query("UPDATE file_handler SET server_file_name = \"".$server_file_name."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$id);
        $this->id = $id;
        // $this->load();
        return $this->id;
    }


    /**
     * Benyttes til at opdaterer oplysninger om fil
     *
     * @param array $input array med input
     *
     * @return integer
     */
    public function update($input)
    {
        $db = new DB_Sql;

        if (!is_array($input)) {
            throw new Exception("Input skal være et array i FileHandler->updateInstance");
        }

        $input = safeToDb($input);
        $validator = new Ilib_Validator($this->error);

        $sql = array();

        $sql[] = 'date_changed = NOW()';

        // do not delete the following - used by electronic invoice
        if (isset($input['file_name'])) {
            $sql[] = 'file_name = "'.$input['file_name'].'"';
        }

        if (isset($input['server_file_name'])) {
            $sql[] = 'server_file_name = "'.$input['server_file_name'].'"';
        }
        if (isset($input['description'])) {
            $validator->isString($input['description'], 'Fejl i udfyldelsen af beskrivelse', '<strong><em>', 'allow_empty');
            $sql[] = 'description = "'.$input['description'].'"';
        }

        // Vi sikre os at den altid bliver sat
        if ($this->id == 0 && !isset($input['accessibility'])) {
            $input['accessibility'] = 'intranet';
        }

        if (isset($input['accessibility'])) {
            $accessibility_key = array_search($input['accessibility'], $this->accessibility_types);
            if ($accessibility_key === false) {
                throw new Exception("Ugyldig accessibility ".$input['accessibility']." i FileHandler->update");
            }

            $sql[] = 'accessibility_key = '.$accessibility_key;
        }

        if ($this->error->isError()) {
            return false;
        }

        if ($this->id != 0) {
            $db->query("UPDATE file_handler SET ".implode(', ', $sql)." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
        } else {
            $db->query("INSERT INTO file_handler SET ".implode(', ', $sql).", user_id = ".$this->kernel->user->get('id').", intranet_id = ".$this->kernel->intranet->get('id').", date_created = NOW()");
            $this->id = $db->insertedId();
        }

        $this->load();

        return $this->id;
    }

    /**
     * Returns the mimetype
     *
     * @param string $key  the array key you search. See key below
     * @param string $from the place to search for the key, can be either 'key' (integer), 'mime_type' (string), 'extension' (string)
     *
     * @return string
     */
    public function _getMimeType($key, $from = 'key')
    {
        if (empty($this->file_types)) {
            $this->loadMimeTypes();
        }

        if ($from == 'key') {
            if (!is_integer($key)) {
                throw new Exception("Når der skal findes mimetype fra key (default), skal første parameter til FileHandler->_getMimeType være en integer");
            }
            return $this->file_types[$key];
        }

        if (in_array($from, array('mime_type', 'extension'))) {
            foreach ($this->file_types as $file_key => $file_type) {
                if ($file_type[$from] == $key) {
                    // Vi putter lige key med i arrayet
                    $file_type['key'] = $file_key;
                    return $file_type;
                }
            }
        }

        return false;
    }

    /**
     * Returns the mimetype based on the key in the array
     *
     * @param string $key  @todo what is this
     *
     * @return string
     */
    protected function _getMimeTypeFromKey($key)
    {
        return $this->_getMimeType($key, 'key');

    }

    function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Loads the mime types
     *
     * @return boolean true on success
     */
    public function loadMimeTypes()
    {
        require_once 'Intraface/modules/filemanager/FileType.php';
        $filetype = new FileType();
        $this->file_types = $filetype->getList();
        return true;
    }

    /**
     * Moves file to filesystem from temporary @todo to what, the method name should reflect that
     *
     * @return boolean
     */
    public function moveFromTemporary()
    {
        $db = new DB_Sql;
        $db->query("UPDATE file_handler SET temporary = 0 WHERE user_id = ".$this->kernel->user->get('id')." AND id = " . $this->id);
        return true;
    }

    /**
     * Returns RandomKeyGenerator
     *
     * @param integer $length the length of the random key
     * @return object RandomKeyGenerator
     */
    private function getRandomKeyGenerator()
    {
        return new Ilib_RandomKeyGenerator();
    }
}
