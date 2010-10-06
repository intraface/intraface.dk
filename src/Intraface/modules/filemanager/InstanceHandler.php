<?php
/**
 * Instance handler. Klarer h�ndtering af billeder forskellige instancer af billeder.
 *
 * @todo		Der mangler noget der hurtigt kan returnere billedst�rrelsen.
 *				Det skal fx bruges i cms, hvor man vil knytte width og height til
 *				billedet, men hvor man ogs� gerne vil have mulighed for at bestemme
 *				hvor bred billedteksten skal v�re!
 *
 * @package Intraface
 * @author		Sune
 * @version	1.0
 *
 */

class InstanceHandler extends Intraface_Standard
{
    /**
     * @var object
     */
    private $file_handler;

    /**
     * @var string
     */
    private $instance_path;

    /**
     * @var array
     */
    // not in use ! private $instance_types;

    /**
     * @var array allo
     */
    // not in use! private $allowed_transform_image = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * @var integer id
     */
    private $id;

    /**
     * @var object db MDB2 object
     */
    private $db;

    /**
     * Constructor
     *
     * @param object  $file_handler File handler object
     * @param integer $id           Optional id
     *
     * @return void
     */
    function __construct($file_handler, $id = 0)
    {
        if (!is_object($file_handler)) {
            throw new Exception("InstanceHandler kr�ver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (1)");
        }

        $this->file_handler = $file_handler;
        $this->id = (int)$id;
        $this->instance_path = $this->file_handler->getUploadPath().'instance/';


        $this->db = MDB2::singleton(DB_DSN);

        if ($this->file_handler->get('is_image') == 0) {
            // throw new Exception("InstanceHandler kan kun startes, hvis filen er et billede i IntanceHandler->InstanceHandler");
            $this->id = 0;
        }

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * desctructor
     */
    public function __destruct() {
        unset($this->file_handler);
        unset($this->instance_path);
    }

    /**
     * Factory
     *
     * @param object $file_handler File handler
     * @param string $type         the instance type
     * @param array  $param        one or more of crop_width, crop_height, crop_offset_x, crop_offset_y
     *
     * @return object
     */
    function factory($file_handler, $type_name, $param = array())
    {
        if (!is_object($file_handler)) {
            throw new Exception("InstanceHandler kr�ver et filehandler- eller filemanagerobject i InstanceHandler->factory (1)");
        }

        if ((int)$file_handler->get('id') == 0) {
            throw new Exception("Der kan kun laves instance ud en loaded fil i Instance->factory");
        }

        if ($file_handler->get('is_image') == 0) {
            throw new Exception("Filen skal v�re et billede i IntanceHandler->factory");
        }

        $instancehandler = new InstanceHandler($file_handler);
        $type = $instancehandler->checkType($type_name);
        if ($type === false) {
            throw new Exception("Ugyldig type '".$type_name."' i InstanceHandler->factory");
        }

        $db = new DB_sql;
        $db->query("SELECT id FROM file_handler_instance WHERE intranet_id = ".$file_handler->kernel->intranet->get('id')." AND active = 1 AND file_handler_id = ".$file_handler->get('id')." AND type_key = ".$type['type_key']);
        if ($db->nextRecord()) {
            return new InstanceHandler($file_handler, $db->f('id'));
        } else {
            $file_handler->createImage();

            if (!empty($param['crop_width']) && !empty($param['crop_height'])) {
                settype($param['crop_offset_x'], 'integer');
                settype($param['crop_offset_y'], 'integer');
                $file_handler->image->crop($param['crop_width'], $param['crop_height'], $param['crop_offset_x'], $param['crop_offset_y']);
                // first we filter only crop parameters.
                $crop_param = array_intersect_key($param, array('crop_width' =>'', 'crop_height' => '', 'crop_offset_x' => '', 'crop_offset_y' => ''));
                $crop_param_string = serialize($crop_param);
            }
            else {
                $crop_param_string = serialize(array());
            }
            $file = $file_handler->image->resize($type['max_width'], $type['max_height'], $type['resize_type']);

            if (!is_file($file)) {
                throw new Exception("Filen blev ikke opretett i InstanceHandler->factory");
            }

            $file_size = filesize($file);
            $imagesize = getimagesize($file);
            $width = $imagesize[0]; // imagesx($file);
            $height = $imagesize[1]; // imagesy($file);

            $db->query("INSERT INTO file_handler_instance SET
                intranet_id = ".$file_handler->kernel->intranet->get('id').",
                file_handler_id = ".$file_handler->get('id').",
                date_created = NOW(),
                date_changed = NOW(),
                type_key = ".$type['type_key'].",
                file_size = ".(int)$file_size.",
                width = ".(int)$width.",
                height = ".(int)$height.", " .
                "crop_parameter = \"".safeToDb($crop_param_string)."\"");

            $id = $db->insertedId();

            $mime_type = $file_handler->get('file_type');
            $server_file_name = $id.'.'.$mime_type['extension'];

            if (!is_dir($instancehandler->instance_path)) {
                if (!mkdir($instancehandler->instance_path, 0755)) {
                    $this->delete();
                    throw new Exception("Kunne ikke oprette mappe i InstanceHandler->factory");
                }
            }

            if (!rename($file, $instancehandler->instance_path.$server_file_name)) {
                throw new Exception("Det var ikke muligt at flytte fil i InstanceHandler->factory");
            }

            if (!chmod($instancehandler->instance_path.$server_file_name, 0644)) {
                // please do not stop executing here
                throw new Exception("Unable to chmod file '".$instancehandler->instance_path.$server_file_name."'");
            }

            $db->query("UPDATE file_handler_instance SET server_file_name = \"".$server_file_name."\", active = 1 WHERE intranet_id = ".$file_handler->kernel->intranet->get('id')." AND id = ".$id);

            return new InstanceHandler($file_handler, $id);
        }
    }


    /**
     * Henter en instance af et billede.
     *
     * @return boolean true or false
     */
    private function load()
    {

        $db = new DB_sql;
        $db->query("SELECT * FROM file_handler_instance WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND active = 1 AND id = ".$this->id);
        if (!$db->nextRecord()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return false;
        }
        $type = $this->checkType((int)$db->f('type_key'), 'type_key');
        if ($type === false) {
            $this->id = 0;
            $this->value['id'] = 0;
            return false;
        }

        $this->value['instance_properties'] = $type;
        $this->value['type'] = $type['name'];

        $this->id = $db->f('id');
        $this->value['id'] = $db->f('id');
        $this->value['date_created'] = $db->f('date_created');
        $this->value['date_changed'] = $db->f('date_changed');


        //$this->value['predefined_size'] = $db->f('predefined_size');
        $this->value['server_file_name'] = $db->f('server_file_name');
        $this->value['file_size'] = $db->f('file_size');
        $this->value['file_path'] = $this->instance_path . $db->f('server_file_name');

        $this->value['last_modified'] = filemtime($this->get('file_path'));
        // $this->value['file_uri'] = FILE_VIEWER.'?id='.$this->get('id').'&type='.$this->get('type').'&name=/'.urlencode($this->file_handler->get('file_name'));
        $this->value['file_uri'] = FILE_VIEWER.'?/'.$this->file_handler->kernel->intranet->get('public_key').'/'.$this->file_handler->get('access_key').'/'.$this->get('type').'/'.urlencode($this->file_handler->get('file_name'));

        // dette er vel kun i en overgangsperiode? LO
        // Det kan lige s� godt v�re der altid. Det g�r jo ingen skade /Sune (20/11 2007)
        if ($db->f('width') == 0) {
            $imagesize = getimagesize($this->get('file_path'));
            $this->value['width'] = $imagesize[0]; // imagesx($this->get('file_uri'));
            $db2 = new DB_sql;
            $db2->query("UPDATE file_handler_instance SET width = ".$this->value['width']." WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND id = ".$this->id);
        } else {
            $this->value['width'] = $db->f('width');
        }

        if ($db->f('height') == 0) {
            $imagesize = getimagesize($this->get('file_path'));
            $this->value['height'] = $imagesize[1]; //imagesy($this->get('file_uri'));
            $db2 = new DB_sql;
            $db2->query("UPDATE file_handler_instance SET height = ".$this->value['height']." WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND id = ".$this->id);
        } else {
            $this->value['height'] = $db->f('height');
        }

        return true;
    }

    /**
     * Returns an array with instance types included information about the instance
     *
     * @return array
     */
    function getList($show = 'visible')
    {
        if (!in_array($show, array('visible', 'include_hidden'))) {
            throw new Exception('First parameter to InstanceManager->getList should either be visibe or include_hidden');
            exit;
        }

        $db = new DB_Sql;
        require_once('Intraface/modules/filemanager/InstanceManager.php');
        $instancemanager = new InstanceManager($this->file_handler->kernel);
        $types = $instancemanager->getList($show);
        $i = 0;
        // if filehander has an id we supply the file information to the array.
        if ($this->file_handler->get('id') != 0) {
            $result = $this->db->query("SELECT id, width, height, type_key, file_size FROM file_handler_instance WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND file_handler_id = ".$this->file_handler->get('id')." AND active = 1 ORDER BY type_key");
            if (PEAR::isError($result)) {
                throw new Exception("Error in query: ".$result->getUserInfo());
                exit;
            }
            $file_instances = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

            $this->file_handler->createImage();

            for ($i = 0, $max = count($types); $i < $max; $i++) {

                $types[$i]['width'] = '';
                $types[$i]['height'] = '';
                $types[$i]['file_size'] = '-';
                $types[$i]['file_uri'] = FILE_VIEWER.'?/'.$this->file_handler->kernel->intranet->get('public_key').'/'.$this->file_handler->get('access_key').'/'.$types[$i]['name'].'/'.urlencode($this->file_handler->get('file_name'));

                $match_file_instance_key = false;
                foreach ($file_instances as $file_instance_key => $file_instance) {
                    if ($file_instance['type_key'] == $types[$i]['type_key']) {
                        $match_file_instance_key = $file_instance_key;
                        break;
                    }
                }

                if ($match_file_instance_key !== false) {
                    $types[$i]['width'] = $file_instances[$match_file_instance_key]['width'];
                    $types[$i]['height'] = $file_instances[$match_file_instance_key]['height'];
                    $types[$i]['file_size'] = $file_instances[$match_file_instance_key]['file_size'];
                } elseif (isset($types[$i]['max_width']) && isset($types[$i]['max_height']) && isset($types[$i]['resize_type']) && $types[$i]['resize_type'] == 'strict') {
                    $types[$i]['width'] = $types[$i]['max_width'];
                    $types[$i]['height'] = $types[$i]['max_height'];
                } elseif (isset($types[$i]['max_width']) && isset($types[$i]['max_height'])) {
                    $tmp_size = $this->file_handler->image->getRelativeSize($types[$i]['max_width'], $types[$i]['max_height']);
                    $types[$i]['width'] = $tmp_size['width'];
                    $types[$i]['height'] = $tmp_size['height'];
                }
            }
        }

        return $types;
    }

    /**
     * check the instance type
     *
     * @param string $type name of type
     * @param string $compare either 'name' or 'type_key'
     *
     * @return mixed array with the type or false on failure;
     */
    public function checkType($type, $compare = 'name')
    {
        if (!in_array($compare, array('name', 'type_key'))) {
            throw new Exception('Second parameter to InstanceHander->checkType should be either name or type_key');
            return false;
        }

        require_once 'Intraface/modules/filemanager/InstanceManager.php';
        $instancemanager = new InstanceManager($this->file_handler->kernel);
        $instance_types = $instancemanager->getList('include_hidden');

        for ($i = 0, $max = count($instance_types); $i < $max; $i++) {
            if (isset($instance_types[$i][$compare]) && $instance_types[$i][$compare] == $type) {
                return $instance_types[$i];
                exit;
            }
        }
        return false;
    }

    /**
     * Deletes an instance
     *
     * @return boolean
     */
    function delete()
    {
        if ($this->id == 0) {
            return false;
        }

        $db = new DB_Sql;

        if (file_exists($this->get('file_path'))) {
            if (!rename($this->get('file_path'), $this->instance_path.'_deleted_'.$this->get('server_file_name'))) {
                throw new Exception("Kunne ikke omd�be filen i InstanceHandler->delete()");
            }
        }

        $db->query("UPDATE file_handler_instance SET active = 0 WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND id = ".$this->id);
        return true;
    }

    /**
     * Deletes all instances for a file
     *
     * @param boolean
     */
    function deleteAll() {
        if ($this->file_handler->get('id') == 0) {
            throw new Exception('An file_handler_id is needed to delete instances in InstanceHandler->deleteAll()');
        }

        $db = new DB_sql;
        $db->query("SELECT id FROM file_handler_instance WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND file_handler_id = ".$this->file_handler->get('id')." AND active = 1");
        while($db->nextRecord()) {
            $instance = new InstanceHandler($this->file_handler, $db->f('id'));
            $instance->delete();
        }

        return true;
    }

    /**
     * deletes all instances of a type
     *
     * @param string $instance   instance representation either name or type_key depending on next parameter
     * @param string $compare either 'name' or 'type_key'
     *
     */
    public function deleteInstanceType($instance, $compare = 'name')
    {
        throw new Exception('so fare not used!');
        exit;
        $type = $this->checkType($instance, $compare);
        $db = new DB_sql;
        $db->query("SELECT id FROM file_handler_instance WHERE intranet_id = ".$this->file_handler->kernel->intranet->get('id')." AND type_key = ".intval($type['type_key'])." AND active = 1");
        while ($db->nextRecord()) {
            $instance = new InstanceHandler($this->file_handler, $db->f('id'));
            $instance->delete();
        }

        return true;
    }
}
