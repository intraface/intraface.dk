<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Htmltext extends CMS_Element
{
    protected $clean_text;
    protected $allowed_tags;

    public function __construct($section, $id = 0)
    {
        $this->value['type'] = 'htmltext';
        parent::__construct($section, $id);
    }

    protected function load_element()
    {
        $this->value['text'] = $this->parameter->get('text');
        $this->value['saved_with'] = $this->parameter->get('saved_with');

        if ($this->value['saved_with'] == 'tinymce') {
            $this->value['html'] = $this->parameter->get('text');
        } elseif ($this->value['saved_with'] == 'wiki') {
            $wiki = new Text_Wiki();

            // when rendering XHTML, make sure wiki links point to a
            // specific base URL
            $wiki->setRenderConf('xhtml', 'wikilink', 'view_url', $this->section->cmspage->get('url'));
            /*
            // set an array of pages that exist in the wiki
            // and tell the XHTML renderer about them
            $pages = array('HomePage', 'AnotherPage', 'SomeOtherPage');
            $wiki->setRenderConf('xhtml', 'wikilink', 'pages', $pages);
            */

            // transform the wiki text into XHTML
            $xhtml = $wiki->transform($this->parameter->get('text'), 'Xhtml');

            $this->value['html'] = $xhtml;
        } else {
            $this->value['html'] = autoop($this->parameter->get('text'));
        }
    }

    /**
     * Vi validerer ikke fordi vi laver purify p� submit - og da alt
     * er tilladt kan det vist ikke rigtig betale sig.
     *
     */

    protected function validate_element($var)
    {
        if ($this->error->isError()){
            return false;
        }

        return true;
    }

    private static function convertArrayToTags($array)
    {
        $tags = '';
        foreach ($array AS $tag) {
            $tags .= '<'.$tag.'>';
        }
        return $tags;
    }

    protected function save_element($var)
    {
        // should probably purify
        require_once 'HTMLPurifier/Bootstrap.php';
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core', 'Encoding', 'ISO-8859-1');
        $config->set('HTML', 'Doctype', 'XHTML 1.0 Strict');
        // only used until we change encoding to utf8
        $purifier_cache_dir = PATH_CACHE.'htmlpurifier/';
        
        if(!is_dir($purifier_cache_dir)) {
            mkdir($purifier_cache_dir);
            if(!is_dir($purifier_cache_dir)) {
                trigger_error('Unable to create HTML Purifier cache dir!', E_USER_ERROR);
                exit;
            }
        }
        $config->set('Cache', 'SerializerPath', $purifier_cache_dir);

        $purifier = new HTMLPurifier($config);
        $clean_text = $purifier->purify($var['text']);

        $this->parameter->save('saved_with', $this->kernel->setting->get('user', 'htmleditor'));
        $this->parameter->save('text', $clean_text);

        return true;
    }
}