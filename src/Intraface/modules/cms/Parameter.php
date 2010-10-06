<?php
/**
 * @package Intraface_CMS
 */
require_once 'Intraface/functions.php';

class CMS_Parameter
{
    /**
     * @var object
     */
    public $object;

    /**
     * @var array
     */
    private $types = array(
        0 => '_invalid_',
        1 => 'cms_section',
        2 => 'cms_element',
        3 => 'cms_template_section'
    );

    /**
     * @var boolean
     */
    private $loaded = false;

    /**
     * @var array
     */
    protected $value = array();

    /**
     * Constructor
     *
     * @param object $object Different objects
     *
     * @return void
     */
    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new Exception('CMS_Parameter::__construct needs an object');
        }

        $this->object = $object;

        switch (strtolower($object->get('identify_as'))) {
            case 'cms_element':
                $this->type_key = array_search('cms_element', $this->types);
            break;
            case 'cms_section':
                $this->type_key = array_search('cms_section', $this->types);
            break;
            case 'cms_template_section':
                $this->type_key = array_search('cms_element', $this->types);
            break;

            default:
                throw new Exception('CMS_Parameter::__construct unknown type');
            break;
        }

        if ($this->object->get('id') > 0) {
            $this->load();
        }

    }

    /**
     * Gemmer parametre til et element
     *
     * @param string $parameter Which parameter to save
     * @param string $value     Which value to save
     *
     * @return boolean
     */
    public function save($parameter, $value)
    {
        if ($this->object->get('id') == 0) {
            throw new Exception('Parameter::save() object cannot be 0 - problems in ' . get_class($this->object));
        }

        # mangler noget validering - skal sikkert kunne s�ttes fra elementet?
        $parameter = safeToDb($parameter);
        $value = safeToDb($value);
        $old_parameter = $this->get($parameter);

        $db = new DB_Sql;

        // hvis parameteren tidligere er oprettet opdateres den!
        if (!empty($old_parameter)) {
            $db->query("UPDATE cms_parameter SET value='".$value."' WHERE type_key = ".$this->type_key." AND intranet_id = ".$this->object->kernel->intranet->get('id')." AND belong_to_id=". $this->object->get('id') . " AND parameter='".$parameter."'");
        } elseif (!empty($value) AND empty($old_parameter)) {
            // hvis parameteren ikke findes oprettes den
            $db->query("INSERT INTO cms_parameter SET type_key=".$this->type_key.", belong_to_id = '".$this->object->get('id')."', parameter='".$parameter."', value='".$value."', intranet_id = ".$this->object->kernel->intranet->get('id'));
        } elseif (empty($value) AND !empty($old_parameter)) {
            // hvis parametervvalue er tom skal den gamle parameter slettes
            $db->query("DELETE FROM cms_parameter WHERE belong_to_id = '".$this->object->get('id')."' AND parameter='".$parameter."' AND intranet_id = ".$this->object->kernel->intranet->get('id') . " AND type_key =" .$this->type_key);
        }

        $this->load();

        return true;
    }

    /**
     * Denne funktion loader alle de n�dvendige parametre til et element ind i et array.
     * Elementerne hentes derefter med get();
     *
     * @return boolean
     */
    private function load()
    {
        if ($this->object->get('id') == 0) {
            throw new Exception('Parameter::save() object cannot be 0 - problems with ' . get_class($this->object));
        }

        $db = new DB_Sql;
        $sql = "SELECT parameter, value FROM cms_parameter WHERE intranet_id = ".$this->object->kernel->intranet->get('id')." AND belong_to_id = " . $this->object->get('id') . " AND type_key = " . $this->type_key; //  . " AND parameter = '"  .$parameter . "'"
        $db->query($sql);

        while($db->nextRecord()) {
            $this->value[$db->f('parameter')] = $db->f('value');
        }
        $this->loaded = true;
        return true;
    }

    /**
     * Get parameter
     *
     * @param string $parameter Which parameter to get
     *
     * @return string
     */
    public function get($parameter)
    {
        if (!$this->loaded AND $this->object->getId() > 0) {
            $this->load();
        }
        if (array_key_exists($parameter, $this->value)) {
            return $this->value[$parameter];
        } else {
            return '';
        }
    }
}