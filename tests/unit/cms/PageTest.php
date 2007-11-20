<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';

class FakeCMSPageSite
{
    public $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function get()
    {
        return 1;
    }
}

class PageTest extends PHPUnit_Framework_TestCase
{

    private $page;

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE cms_template');
        $db->query('TRUNCATE cms_page');
        $this->page = new CMS_Page($this->createSite());

    }

    function createKernel()
    {
        $this->kernel = new Kernel;
        $this->kernel->intranet = new FakeCMSIntranet;
        $this->kernel->user = new FakeCMSIntranet;
        $this->kernel->setting = new FakeCMSSetting;
        $this->kernel->module('cms');

        return $this->kernel;
    }

    function createSite()
    {
        return new FakeCMSPageSite($this->createKernel());
    }

    function createTemplate() {
        $kernel = new FakeCMSKernel();
        $site = new FakeCMSSite($kernel);
        $template = new CMS_Template($site);

        $template->save(array('name' => 'test', 'identifier' => 'test'));
        
        $this->assertEquals('', $template->error->view());
        // $template->getKeywords();

        // here we should add som keywords to the template.
        
        return $template->get('id');
    }



    function testConstruction()
    {
        $this->assertTrue(is_object($this->page));
    }

    /*
    function testSaveSucceedsWithValidValues()
    {
        $site = new CMS_Site($this->kernel);
        $site_array = array(
            'name' => 'Tester',
            'url' => 'http://localhost/',
            'cc_license' => '1'
        );
        $site->save($site_array);
        $this->assertEquals($site_array['name'], $site->get('name'));
        $this->assertEquals($site_array['url'], $site->get('url'));
        $this->assertEquals($site_array['cc_license'], $site->get('cc_license'));
    }
    */

    function testSaveWithSuccessWithTemplateWithKeywords() {

        $template_id = $this->createTemplate();

        $input = array(
            'allow_comments' => 1,
            'hidden' => 1,
            'page_type' => 'page',
            'identifier' => 'test',
            'navigation_name' => 'test',
            'title' => 'test',
            'keywords' => 'search, words',
            'description' => 'test page',
            'template_id' => $template_id);

        $this->assertTrue($this->page->save($input) > 0);

    }
}
?>