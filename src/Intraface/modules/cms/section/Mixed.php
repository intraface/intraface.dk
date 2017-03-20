<?php
/**
 * Mixed Section
 *
 * @package Intraface_CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_modules_cms_section_Mixed extends CMS_Section
{
    function __construct($cmspage, $id = 0)
    {
        $this->value['type'] = 'mixed';
        parent::__construct($cmspage, $id);
    }

    function load_section()
    {
        //$this->value['html'] = $this->getSectionHtml();
        foreach ($this->getElements() as $element) {
            $this->value['elements'][] = $element->get();
        }
        return true;
    }

    function validate_section($var)
    {
        return true;
    }

    function save_section($var)
    {
        return true;
    }

    /**
     * @todo - tror den her er med til at for�rsage mange sql kald -
     *         could probably be optimized quite a bit.
     */
    function getElements()
    {
        $element = array();
        $sql_expire = '';
        $sql_publish = '';
        if (!is_object($this->kernel->user)) {
            $sql_expire = " AND (date_expire > NOW() OR date_expire = '0000-00-00 00:00:00')";
            $sql_publish = " AND date_publish < NOW()";
        }

        $db = new DB_Sql;
        $db->query("SELECT id FROM cms_element
            WHERE intranet_id = ".$this->kernel->intranet->get('id')."
                AND section_id = " . $this->id . "
                AND active = 1 " . $sql_expire . $sql_publish . "
            ORDER BY position ASC");
        $i = 0;

        while ($db->nextRecord()) {
            $element[$i] = CMS_Element::factory($this, 'section_and_id', $db->f('id'));
            $i++;
        }
        return $element;
    }
}
