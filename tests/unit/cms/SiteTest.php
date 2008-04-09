<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';

class SiteTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE cms_site');
        
        $this->kernel = $this->createKernel();

    }

    function createKernel()
    {
        $this->kernel = new Kernel;
        $this->kernel->intranet = new FakeCMSIntranet;
        $this->kernel->setting = new FakeCMSSetting;
        $this->kernel->module('cms');

        return $this->kernel;
    }

    function testConstruction()
    {
        $site = new CMS_Site($this->kernel);
        $this->assertTrue(is_object($site));
    }

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

}
?>