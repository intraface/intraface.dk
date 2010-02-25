<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Twitter extends CMS_Element
{
    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'twitter';
        parent::__construct($section, $id);
    }

    function load_element()
    {
        $this->value['search'] = $this->parameter->get('search');
        $this->value['number'] = $this->parameter->get('number');
        $twitterSearch  = new Zend_Service_Twitter_Search('json');
        $this->value['results'] = $twitterSearch->search($this->parameter->get('search'), array('rpp' => $this->parameter->get('number')));
    }

    /**
     *
     */
    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    function save_element($var)
    {
        $this->parameter->save('search', $var['search']);
        $this->parameter->save('number', $var['number']);

        return true;
    }
}
