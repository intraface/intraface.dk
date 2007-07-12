<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'CMSStubs.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/cms/element/PageList.php';

class PageElementTest extends PHPUnit_Framework_TestCase {

    function createPageList()
    {
        $kernel = new FakeCMSKernel();
        $site = new FakeCMSSite($kernel);
        $page = new FakeCMSPage($site);
        $section = new FakeCMSSection($page);
        $pagelist = new CMS_Pagelist($section);
        return $pagelist;

    }

    function testConstruction()
    {
        $pagelist = $this->createPageList();
        $this->assertTrue(is_object($pagelist));
    }

    function testSave()
    {
        $pagelist = $this->createPageList();

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

        $this->assertTrue($pagelist->save($data) > 0);
    }
}
?>