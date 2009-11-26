<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/modules/cms/Element.php';
require_once 'Intraface/modules/cms/element/Gallery.php';

class GalleryElementTest extends PHPUnit_Framework_TestCase
{
    private $gallery;

    function setUp()
    {
        $kernel = new Stub_Kernel();
        $site = new FakeCMSSite($kernel);
        $page = new FakeCMSPage($site);
        $section = new FakeCMSSection($page);
        $this->gallery = new Intraface_modules_cms_element_Gallery($section);
    }

    function tearDown()
    {
        unset($this->gallery);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->gallery));
    }

    function testSave()
    {
        $data = array(
            'elm_properties' => 'none',
            'elm_adjust' => 'left',
            'elm_width' => '100px',
            'gallery_select_method' => 'single_image',
            'thumbnail_size' => 'small',
            'popup_size' => 'medium',
            'show_description' => true
        );

        $this->assertTrue($this->gallery->save($data) > 0);
    }
}