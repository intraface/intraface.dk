<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Wikitext extends CMS_Element
{
    protected $clean_text;
    protected $allowed_tags;

    public function __construct($section, $id = 0)
    {
        $this->value['type'] = 'wikitext';
        parent::__construct($section, $id);
    }

    protected function load_element()
    {
        $this->value['text'] = $this->parameter->get('text');

        $wiki = new Text_Wiki();

        // when rendering XHTML, make sure wiki links point to a
        // specific base URL
        $wiki->setRenderConf('xhtml', 'wikilink', 'view_url', $this->section->cmspage->get('url'));
        $xhtml = $wiki->transform($this->parameter->get('text'), 'Xhtml');

        $this->value['html'] = $xhtml;
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

    protected function save_element($var)
    {
        // should probably purify
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

        $this->parameter->save('text', $clean_text);

        return true;
    }
}