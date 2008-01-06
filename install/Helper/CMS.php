<?php
class Install_Helper_CMS {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function createSiteWithTemplate() {
        require_once 'Intraface/modules/cms/Site.php';
        $site = new CMS_Site($this->kernel);
        $site_id = $site->save(array('name' => 'Test', 'url' => 'http://localhost/'));
        
        require_once 'Intraface/modules/cms/Template.php';
        $template = new CMS_Template($site);
        $template_id = $template->save(array('name' => 'Test', 'identifier' => 'test'));
        
        require_once 'Intraface/modules/cms/TemplateSection.php';
        require_once 'Intraface/modules/cms/template_section/Mixed.php';
        $section = new CMS_Template_Mixed($template);
        $section_id = $section->save(array('name' => 'Test', 'identifier' => 'test', 'allowed_element' => array(1, 2, 3, 4, 5, 6, 7, 8, 9)));
        
    }
    
    public function createPageWithMixedSection() {
        
        require_once 'Intraface/modules/cms/Site.php';
        $site = new CMS_Site($this->kernel);
        $site_id = $site->save(array('name' => 'Test', 'url' => 'http://localhost/'));
        
        require_once 'Intraface/modules/cms/Template.php';
        $template = new CMS_Template($site);
        $template_id = $template->save(array('name' => 'Test', 'identifier' => 'test'));
        
        require_once 'Intraface/modules/cms/TemplateSection.php';
        require_once 'Intraface/modules/cms/template_section/Mixed.php';
        $section = new CMS_Template_Mixed($template);
        $section_id = $section->save(array('name' => 'Test', 'identifier' => 'test', 'allowed_element' => array(1, 2, 3, 4, 5, 6, 7, 8, 9)));
        
        require_once 'Intraface/modules/cms/Page.php';
        $page = new CMS_Page($site);
        $template_id = $page->save(array('title' => 'Test', 'allow_comment' => 0, 'hidden' => 0, 'page_type' => 'page', 'template_id' => $template_id));
        
    }
}
?>
