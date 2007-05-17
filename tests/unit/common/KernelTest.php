<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';

class KernelTest extends PHPUnit_Framework_TestCase
{

    function testRandomKey()
    {
        $this->assertTrue(strlen(Kernel::randomKey(9)) == 9);
    }

    /*
    function testWeblogin()
    {
        $session_id = 'sesssdfion';
        $kernel = new Kernel;
        $this->assertFalse($kernel->weblogin('private', 'wrongkey', $session_id));
        $this->assertTrue($kernel->weblogin('private', 'privatekeyshouldbereplaced', $session_id));
        $this->assertEquals(get_class($kernel->weblogin), 'Weblogin');
        $this->assertEquals(get_class($kernel->intranet), 'Intranet');
        $this->assertEquals(get_class($kernel->setting), 'Setting');
        $this->assertTrue($kernel->weblogin('public', 'publickeyshouldbereplaced', $session_id));
        $this->assertEquals(get_class($kernel->weblogin), 'Weblogin');
        $this->assertEquals(get_class($kernel->intranet), 'Intranet');
        $this->assertEquals(get_class($kernel->setting), 'Setting');
    }
    */

    function tearDown()
    {
    }

}
?>