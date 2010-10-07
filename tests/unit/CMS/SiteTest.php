<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'CMSStubs.php';
require_once 'Intraface/modules/cms/Site.php';

class SiteTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE cms_site');

        $this->kernel = $this->createKernel();

    }

    function createKernel()
    {
        $this->kernel = new Stub_Kernel;
        $this->kernel->setting->set('intranet', 'cms.stylesheet.default', 'some.css');
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