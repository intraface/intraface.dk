<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'XML/RPC2/Client.php';

class CMSServerTest extends PHPUnit_Framework_TestCase
{

    function testNotStartedYet()
    {
        $this->markTestIncomplete('not started yet');
    }



}
?>