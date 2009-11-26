<?php
require_once 'Intraface/Kernel.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/cms/Page.php';

class FakeCMSPage extends CMS_Page {
    public $kernel;
    public $cmssite;
    public $dbquery;
    function __construct($site) {
        $this->cmssite = $site;
        $this->kernel = $site->kernel;
        $this->dbquery = new Intraface_DBQuery($this->kernel, 'cms_page', 'cms_page.intranet_id = '.$this->kernel->intranet->get('id').' AND cms_page.active = 1 AND site_id = ' . $this->cmssite->get('id'));
    }
    function get() {
        return 1;
    }
}

class FakeCMSSite {
    public $kernel;
    function __construct($kernel) {
        $this->kernel = $kernel;
    }
    function get() {
        return 1;
    }
}

class FakeCMSSection {
    public $kernel;
    public $cmssite;
    function __construct($page) {
        $this->cmspage = $page;
        $this->kernel = $this->cmspage->kernel;
    }
    function get() {
        return 1;
    }
}

class FakeCMSTemplate
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new Stub_Kernel;
        $this->cmssite = new FakeCMSSite($this->kernel);
    }

    function get()
    {
        return 1;
    }
}

class FakeCMSTemplateSection
{
    public $cmssite;

    function __construct($site)
    {
        $this->site = $site;
    }

    function get()
    {
        return '';
    }
}
