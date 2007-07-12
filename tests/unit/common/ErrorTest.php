<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Error.php';

class ErrorTest extends PHPUnit_Framework_TestCase
{
    function testConstruction()
    {
        $error = new Error;
    }

}
?>
