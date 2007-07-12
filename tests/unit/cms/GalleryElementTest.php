<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/element/Gallery.php';

class GalleryElementTest extends PHPUnit_Framework_TestCase {

    function createGallery()
    {
        $kernel = new FakeCMSKernel();
        $site = new FakeCMSSite($kernel);
        $page = new FakeCMSPage($site);
        $section = new FakeCMSSection($page);
        $pagelist = new CMS_Gallery($section);
        return $pagelist;

    }

    function testConstruction()
    {
        $pagelist = $this->createGallery();
        $this->assertTrue(is_object($pagelist));
    }

    function testSave()
    {
        $pagelist = $this->createGallery();

        $data = array(
            'elm_properties' => 'none',
            'elm_adjust' => 'left',
            'elm_width' => '100px',
            'gallery_select_method' => 'single_image',
            'thumbnail_size' => 'small',
            'popup_size' => 'medium',
            'show_description' => true
        );

        $this->assertTrue($pagelist->save($data) > 0);
    }
}
?>