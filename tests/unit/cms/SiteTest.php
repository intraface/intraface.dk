<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';

class SiteTest extends PHPUnit_Framework_TestCase {

    function testConstruction() {
        $this->markTestIncomplete('not completed');
    }
    /*
    function setUp() {
        $this->kernel = new Kernel;
        $this->kernel->intranet = new FakeIntranet;
        $this->kernel->setting = new FakeSetting;
        $this->kernel->module('cms');

    }

    function testSaveSucceedsWithValidValues() {
        $site = new CMS_Site($this->kernel);
        $site_array = array(
            'name' => 'Tester',
            'url' => 'http://localhost/',
            'cc_license' => '1'
        );
        $site->save($site_array);
        $this->assertEqual($site_array['name'], $site->get('name'));
        $this->assertEqual($site_array['url'], $site->get('url'));
        $this->assertEqual($site_array['cc_license'], $site->get('cc_license'));
    }
    */

}
?>