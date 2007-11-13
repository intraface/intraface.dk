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
        $this->page = new CMS_Page($this->createSite());

    }

    function createKernel()
    {
        $this->kernel = new Kernel;
        $this->kernel->intranet = new FakeCMSIntranet;
        $this->kernel->setting = new FakeCMSSetting;
        $this->kernel->module('cms');

        return $this->kernel;
    }

    function createSite()
    {
        return new FakeCMSPageSite($this->createKernel());
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
}
?>