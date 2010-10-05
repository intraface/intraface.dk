<?php
/**
 * @package Intraface_CMS
 */
class CMS_Stylesheet extends Intraface_Standard
{
    private $cmssite;
    public $error;

    function __construct($cmssite)
    {
        if (!is_object($cmssite) OR strtolower(get_class($cmssite)) != 'cms_site') {
            throw new Exception('CMS_Stylesheet::__construct needs CMS_Site - got ' . get_class($cmssite));
        }
        $this->cmssite = $cmssite;
        $this->error = new Intraface_Error;
        $this->load();

    }

    function validate($input)
    {
        /*
        $validate_string = VALIDATE_ALPHA . VALIDATE_NUM . '{}:*';
        if (!Validate::string($input['css'], array('format' => $validate_string))) {
            $this->error->set('Der er brugt ulovlige tegn - du kan kun bruge f�lgende tegn '.$validate_string);
            return 0;
        }
        */
        return 1;
    }

    function save($input)
    {
        //$input = safeToDb($input);
        if (!$this->validate($input)) {
            return 0;
        }
        $this->cmssite->kernel->setting->set('intranet', 'cms.stylesheet.site', $input['css'], $this->cmssite->get('id'));

        $this->load();

        return 1;
    }

    function load()
    {
        if ($this->cmssite->id == 0) {
            $this->cmssite->load();
        }
        $this->value['css']  = $this->cmssite->kernel->setting->get('intranet', 'cms.stylesheet.default');
        //$this->value['css_own'] = $this->cmssite->kernel->setting->get('intranet', 'cms.stylesheet.site', $this->cmssite->get('id'));
        $this->value['css_own'] = $this->cmssite->kernel->setting->get('intranet', 'cms.stylesheet.site', $this->cmssite->get('id'));
        $this->value['css'] .= $this->value['css_own'];
        $this->value['header'] = 'Content-type: text/css';
    }

}
