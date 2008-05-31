<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Error.php';

/**
 * Remember this is only test for if the extend works. The test of the actual functionality is in Intraface_3Party
 */

class ErrorTest extends PHPUnit_Framework_TestCase
{
    function testConstruction()
    {
        $error = new Intraface_Error;
        $this->assertTrue(is_object($error));
    }

}
