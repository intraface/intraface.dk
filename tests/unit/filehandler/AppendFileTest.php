<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/shared/filehandler/AppendFile.php';

class FakeFileHandler
{
    function get()
    {
        return 1;
    }
}

class AppendFileTest extends PHPUnit_Framework_TestCase
{
    function createAppendFile()
    {
        return new AppendFile(new Kernel, 'product', 1);
    }

    function testConstruction()
    {
        $append = $this->createAppendFile();
        $this->assertTrue(is_object($append));
    }

}
?>