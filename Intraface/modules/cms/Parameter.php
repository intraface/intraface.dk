<?php
require_once 'Intraface/Standard.php';
require_once 'Intraface/functions/functions.php';

class CMS_Parameter extends Standard {

    var $object;
    var $types = array(
        0 => '_invalid_',
        1 => 'cms_section',
        2 => 'cms_element',
        3 => 'cms_template_section'
    );
    var $loaded = false; // bruges fordi objekterne kan �ndre sige
    var $value = array();

    function __construct($object) {

        if (!is_object($object)) {
            trigger_error('CMS_Parameter::__construct needs an object', E_USER_ERROR);
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
                trigger_error('CMS_Parameter::__construct unknown type', E_USER_ERROR);
            break;
        }

        if ($this->object->get('id') > 0) {
            $this->load();
        }

    }

    /**
     * Gemmer parametre til et element
     */
    function save($parameter, $value) {

        if ($this->object->get('id') == 0) {
            trigger_error('Parameter::save() object cannot be 0 - problems in ' . get_class($this->object), E_USER_ERROR);
        }

        # mangler noget validering - skal sikkert kunne s�ttes fra elementet?
        $parameter = safeToDb($parameter);
        $value = safeToDb($value);
        $old_parameter = $this->get($parameter);

        $db = new DB_Sql;

        // hvis parameteren tidligere er oprettet opdateres den!
        if (!empty($old_parameter)) {
            $db->query("UPDATE cms_parameter SET value='".$value."' WHERE type_key = ".$this->type_key." AND intranet_id = ".$this->object->kernel->intranet->get('id')." AND belong_to_id=". $this->object->get('id') . " AND parameter='".$parameter."'");
        }
        // hvis parameteren ikke findes oprettes den
        elseif (!empty($value) AND empty($old_parameter)) {
            $db->query("INSERT INTO cms_parameter SET type_key=".$this->type_key.", belong_to_id = '".$this->object->get('id')."', parameter='".$parameter."', value='".$value."', intranet_id = ".$this->object->kernel->intranet->get('id'));
        }
        // hvis parameterv�rdien er tom skal den gamle parameter slettes
        elseif (empty($value) AND !empty($old_parameter)) {
            $db->query("DELETE FROM cms_parameter WHERE belong_to_id = '".$this->object->get('id')."' AND parameter='".$parameter."' AND intranet_id = ".$this->object->kernel->intranet->get('id') . " AND type_key =" .$this->type_key);
        }

        return 1;
    }

    /**
     * Denne funktion loader alle de n�dvendige parametre til et element ind i et array.
     * Elementerne hentes derefter med get();
     *
     * @access private
     * @see get();
     */
    function load() {

        if ($this->object->get('id') == 0) {
            trigger_error('Parameter::save() object cannot be 0 - problems with ' . get_class($this->object));
        }


        $db = new DB_Sql;
        $sql = "SELECT parameter, value FROM cms_parameter WHERE intranet_id = ".$this->object->kernel->intranet->get('id')." AND belong_to_id = " . $this->object->get('id') . " AND type_key = " . $this->type_key; //  . " AND parameter = '"  .$parameter . "'"
        $db->query($sql);

        while($db->nextRecord()) {
            $this->value[$db->f('parameter')] = $db->f('value');
        }
        $this->loaded = true;
        return 1;
    }

    /**
     * @access public
     * @see load();
     */

    function get($parameter) {
        if (!$this->loaded AND $this->object->id > 0) {
            $this->load();
        }
        if (array_key_exists($parameter, $this->value)) {
            return $this->value[$parameter];
        }
        else {
            return '';
        }

    }


}
?>