<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/shared/filehandler/FileViewer.php';

class FakeFileviewerIntranet {
    function get() {
        return 1;
    }
}

class FakeFileviewerUser {
    function get() {
        return 1;
    }
}
class FakeFileviewerKernel {
    public $intranet;
    public $user;
}

class FileViewerTest extends PHPUnit_Framework_TestCase
{

    function testConstruction()
    {
        $fileviewer = new FileViewer();
        $this->assertTrue(is_object($fileviewer));
    }

    function testParseQueryString()
    {
        $querystring = '?/QH4X9sbRgRyPApgS/Ci7emeihjcJ3WdNyDMz7vspLq5CeT3QEb5IE9SMBUEKHHrnckM/MountKosciuszko_frontpage.jpg';
        $fileviewer = new FileViewer();
        $fileviewer->parseQueryString($querystring);
        $this->assertEquals('QH4X9sbRgRyPApgS', $fileviewer->public_key);
        $this->assertEquals('Ci7emeihjcJ3WdNyDMz7vspLq5CeT3QEb5IE9SMBUEKHHrnckM', $fileviewer->file_key);
        $this->assertEquals('MountKosciuszko_frontpage.jpg', $fileviewer->file_type);
    }

    /*
    function testFetch() {
        $querystring = '?/QH4X9sbRgRyPApgS/Ci7emeihjcJ3WdNyDMz7vspLq5CeT3QEb5IE9SMBUEKHHrnckM/MountKosciuszko_frontpage.jpg';
        $fileviewer = new FileViewer();
        $fileviewer->fetch($querystring);
    }
    */


}
?>