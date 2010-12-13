<?php
require_once dirname(__FILE__) . './../config.test.php';
require_once 'CMSStubs.php';
require_once 'Intraface/modules/cms/Element.php';

class PageElementTest extends PHPUnit_Framework_TestCase
{
    private $pagelist;

    function setUp()
    {
        $kernel = new Stub_Kernel();
        $site = new FakeCMSSite($kernel);
        $page = new FakeCMSPage($site);
        $section = new FakeCMSSection($page);
        $this->pagelist = new Intraface_modules_cms_element_Pagelist($section);
    }

    function tearDown()
    {
        unset($this->pagelist);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->pagelist));
    }

    function testSave()
    {
        $data = array(
            'elm_properties' => 'none',
            'elm_adjust' => 'left',
            'elm_width' => '100px',
            'headline' => 'Test',
            'show_type' => 'article',
            'keyword' => array(1),
            'show' => 1,
            'lifetime' => 20,
            'no_results_text' => 'none',
            'read_more_text' => 'none'
        );

        $this->assertTrue($this->pagelist->save($data) > 0);
    }

    function testSaveWhenUpdating()
    {
        $data = array(
            'elm_properties' => 'none',
            'elm_adjust' => 'left',
            'elm_width' => '100px',
            'headline' => 'Test',
            'show_type' => 'article',
            'keyword' => array(1),
            'show' => 1,
            'lifetime' => 20,
            'no_results_text' => 'none',
            'read_more_text' => 'none'
        );

        $this->assertTrue($this->pagelist->save($data) > 0);

        $data = array(
            'elm_properties' => 'none',
            'elm_adjust' => 'left',
            'elm_width' => '100px',
            'headline' => 'Test',
            'show_type' => 'article',
            'keyword' => array(1),
            'show' => 1,
            'lifetime' => 20,
            'no_results_text' => 'none',
            'read_more_text' => 'none'
        );

        $this->assertTrue($this->pagelist->save($data) > 0);
    }

    function testValidateFailsWhenInvalidValues()
    {
        $data = array(
            'elm_properties' => 'wrongvalue',
            'elm_adjust' => 'wrongvalue',
            'elm_width' => '100wrongvalue',
            'elm_box' => 'wrongvalue',
            'headline' => 'Test',
            'show_type' => 'article',
            'keyword' => array(1),
            'show' => 1,
            'lifetime' => 20,
            'no_results_text' => 'none',
            'read_more_text' => 'none'
        );

        $this->assertTrue($this->pagelist->save($data) == 0);

    }

    function testDeleteReturnsTrue()
    {
        $this->assertTrue($this->pagelist->delete());
    }

    function testUnDeleteReturnsTrue()
    {
        $this->assertTrue($this->pagelist->delete());
        $this->assertTrue($this->pagelist->undelete());
    }
}