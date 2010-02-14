<?php
/**
 * TODO make this abstract so the extending classes does it properly
 *
 * @package Intraface_CMS
 */
require_once 'Intraface/modules/cms/Parameter.php';

abstract class CMS_Element extends Intraface_Standard
{
    public $id;
    public $section;
    public $kernel;
    public $parameter;
    public $element_types;
    public $value;
    public $extra_css;
    public $extra_style;
    public $error;
    public $position;

    public $properties = array(
        'none'    => 'none',
        'newline' => 'break before element',
        'float'   => 'floating'
    );

    public $alignment = array(
        'left'   => 'left',
        'center' => 'center',
        'right'  => 'right'
    );

    /**
     * Constructor
     *
     * @param object  $section Section object
     * @param integer $id      Optional integer
     *
     * @return void
     */
    function __construct($section, $id = 0)
    {
        $this->value['identify_as'] = 'cms_element';  // bruges af parameter

        $this->id        = (int) $id;
        $this->kernel    = $section->kernel;
        $this->section   = $section;
        $this->error     = new Intraface_Error;
        $this->parameter = $this->createParameter();

        $cms_module = $this->section->kernel->module('cms');
        $this->element_types = $cms_module->getSetting('element_types');

        if (is_string($this->value['type']) AND in_array($this->value['type'], $this->element_types)) {
            $this->value['type_key'] = array_search($this->value['type'], $this->element_types);
        }

        if ($this->id > 0) {
            $this->load();
        }
    }

    function getPosition(DB_Sql $db)
    {
        return new Ilib_Position($db, "cms_element", $this->id, "section_id=".$this->section->get('id')." AND active = 1 AND intranet_id = " . $this->kernel->intranet->get('id'), "position", "id");
    }

    /**
     * Creates a parameter
     *
     * @return object
     */
    function createParameter()
    {
        return new CMS_Parameter($this);
    }

    /**
     * Creates an element
     *
     * @todo This should be changed to better names
     *
     * @param object $object A fitting object
     * @param string $type   How do you want to create the element?
     * @param string $value  Which value do you pass
     *
     * @return object
     */
    function factory($object, $type, $value)
    {
        if (get_class($object) == 'Intraface_Kernel') {
            $kernel = $object;
        } else {
            $kernel = $object->kernel;
        }

        $gateway = new Intraface_modules_cms_ElementGateway($kernel, new DB_Sql);

        switch ($type) {
            case 'type':
                return $gateway->findBySectionAndType($object, $value);
                break;
            case 'id':
                return $gateway->findByKernelAndId($object, $value);
                break;
            case 'section_and_id':
                return $gateway->findBySectionAndId($object, $value);
                break;
            default:
                trigger_error('Element::factory:: Invalid type', E_USER_ERROR);
                break;
        }
    }

    /**
     * Loads the values for the Element
     *
     * @return integer
     */
    function load()
    {
        if ($this->id == 0) {
            return 0;
        }

        $db = new DB_Sql;
        $db->query("SELECT id, section_id, date_expire, date_publish, type_key, position FROM cms_element WHERE intranet_id = ".$this->section->kernel->intranet->get('id')." AND id = " . $this->id);
        if (!$db->nextRecord()) {
            return 0;
        }
        $this->value = array(); // Vi nulstiller f�rst alle oplysninger, hvis de tidligere har v�ret loadet.
        $this->value['id'] = $db->f('id');
        $this->value['section_id'] = $db->f('section_id');
        $this->value['date_expire'] = $db->f('date_expire');
        $this->value['date_publish'] = $db->f('date_publish');
        $this->value['type_key'] = $db->f('type_key');
        $this->value['position'] = $db->f('position');
        $this->value['type'] = $this->element_types[$this->value['type_key']];

        $this->value['elm_width'] = $this->parameter->get('elm_width');
        $this->value['elm_box'] = $this->parameter->get('elm_box');
        $this->value['elm_properties'] = $this->parameter->get('elm_properties');
        $this->value['elm_adjust'] = $this->parameter->get('elm_adjust');

        $this->value['extra_style'] = '';
        $this->value['extra_class'] = '';

        if ($this->get('elm_width')) {
            $this->value['extra_style'] .= 'width: ' . $this->get('elm_width') . ';';
        }

        if ($this->get('elm_properties') == 'float') {
            $this->value['extra_class'] .= ' cms-float-'.$this->get('elm_adjust');
            /*
            if ($this->get('type') == 'picture') {
                $this->extra_style .= ' width: ' . $this->get('width') . 'px';
            }
            */
        } elseif ($this->get('elm_properties') == 'newline') {
            $this->value['extra_style'] .= ' clear: both;';

        }
        if ($this->get('elm_adjust')) {
            $this->value['extra_class'] .= ' cms-align-' . $this->get('elm_adjust');
        }

        if ($this->get('elm_box') == 'box') {
            $this->value['extra_class'] .= ' cms-box';
        }

        if (method_exists($this, 'load_element')) {
            $this->load_element();
        }

        return $this->id;
    }

    /**
     * Validates values
     *
     * @param array $var Values to validate
     *
     * @return boolean
     */
    function validate($var)
    {
        // validere om section overhovedet findes
        // validere type
        if (!empty($var['elm_box']) AND $var['elm_box'] != 'box') {
            $this->error->set('error in elm_box');
        }
        if (!array_key_exists($var['elm_properties'], $this->properties)) {
            $this->error->set('error in elm_properties');
        }
        if (!array_key_exists($var['elm_adjust'], $this->alignment)) {
            $this->error->set('error in elm_adjust');
        }
        if (!empty($var['elm_width']) AND !strstr($var['elm_width'], '%') AND !strstr($var['elm_width'], 'em') AND !strstr($var['elm_width'], 'px')) {
            $this->error->set('error in elm_width - use %, em or px');
        }

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    /**
     * Saves values
     *
     * @param array $var Values to save
     *
     * @return boolean
     */
    function save($var)
    {

        if (!isset($var['date_expire'])) {
            $var['date_expire'] = '0000-00-00 00:00';
        }

        if (!$this->validate($var)) {
            return false;
        }

        if (empty($var['date_publish']) OR $var['date_publish'] == '0000-00-00 00:00:00') {
            $date_publish = 'NOW()';
        } else {
            $date_publish = '"'.safeToDb($var['date_publish']).'"';
        }

        $db = new DB_Sql;

        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW()";
        } else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }
        $sql = $sql_type . " cms_element SET
                intranet_id = ".$this->section->kernel->intranet->get('id').",
                section_id=". (int)$this->section->get('id') . ",
                type_key = ".safeToDb($this->value['type_key']).",
                date_changed = NOW(),
                date_publish = ".$date_publish.",
                date_expire = '".safeToDb($var['date_expire'])."'
            " . $sql_end;

        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();

            $next_pos = $this->getPosition($db)->getMaxPosition() + 1;
            $db->query("UPDATE cms_element SET position = " . $next_pos . " WHERE id = " . $this->id);
        }

        $this->load();

        // af en eller anden grund er paramterobjektet ikke en ordentlig referencd
        // derfor loader jeg lige objektet med det rigtige id
        // HACK det er naturligvis et hack, men vi m� kunne finde ud af hvad der er galt
        // det er kun et problem for nye elementer - dem der starter med id = 0
        $this->parameter->object->id = $this->id;
        $this->parameter->object->load();

        $this->parameter->save('elm_width', $var['elm_width']);
        if (isset($var['elm_box'])) {
            $this->parameter->save('elm_box', intval($var['elm_box']));
        }
        $this->parameter->save('elm_properties', $var['elm_properties']);
        $this->parameter->save('elm_adjust', $var['elm_adjust']);

        if (!$this->validate_element($var)) {
            return false;
        }

        $this->save_element($var);

        return $this->id;
  }

    /**
     * Deactivates an element
     *
     * @return boolean
     */
    public function delete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE cms_element SET active = 0 WHERE id = " . $this->id);
        return true;
    }

    /**
     * Reactivates an element
     *
     * @return boolean
     */
    public function undelete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE cms_element SET active = 1 WHERE id = " . $this->id);
        return true;
    }

    public function getId()
    {
        return $this->id;
    }

    abstract protected function validate_element($var);
    abstract protected function save_element($var);
    abstract protected function load_element();
}