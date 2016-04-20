<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Pagelist extends CMS_Element
{

    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'pagelist';
        parent::__construct($section, $id);
    }

    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);
        $validator->isString($var['headline'], 'error in headline', '', 'allow_empty');
        $validator->isString($var['no_results_text'], 'error in no results text', '', 'allow_empty');
        $validator->isString($var['read_more_text'], 'error in read more text', '', 'allow_empty');
        $validator->isString($var['show_type'], 'error in Show type', '', 'allow_empty');
        //$validator->isString($var['show_keyword'], 'error in show keyword', '', 'allow_empty');
        if (isset($var['show'])) {
            $validator->isString($var['show'], 'error in show', '', 'allow_empty');
        }
        //$validator->isString($var['lifetime'], 'error in lifetime', '', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    function save_element($var)
    {
        $this->parameter->save('headline', $var['headline']);
        $this->parameter->save('no_results_text', $var['no_results_text']);
        $this->parameter->save('read_more_text', $var['read_more_text']);
        $this->parameter->save('show_type', $var['show_type']);
        settype($var['keyword'], 'array');
        $this->parameter->save('keyword', serialize($var['keyword']));
        $this->parameter->save('show', $var['show']);
        //$this->parameter->save('lifetime', $var['lifetime']);
        return true;
    }

    function load_element()
    {
        $this->value['headline'] = $this->parameter->get('headline');
        $this->value['read_more_text'] = $this->parameter->get('read_more_text');
        $this->value['no_results_text'] = $this->parameter->get('no_results_text');
        $this->value['show_type'] = $this->parameter->get('show_type');
        $this->value['keyword'] = unserialize($this->parameter->get('keyword'));
        $this->value['show'] = $this->parameter->get('show');
        //$this->value['lifetime'] = $this->parameter->get('lifetime');

        $this->section->cmspage->getDBQuery()->clearAll();
        if (!empty($this->value['show_type'])) {
            $this->section->cmspage->getDBQuery()->setFilter('type', $this->value['show_type']);
        }
        if (!empty($this->value['keyword'])) {
            $this->section->cmspage->getDBQuery()->setKeyword($this->value['keyword']);
        }
        $this->value['pages'] = $this->section->cmspage->getList();
    }
}
