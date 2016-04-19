<?php
class Fake_Auth_Intraface_Intranet
{

}

class WebloginTest extends PHPUnit_Framework_TestCase
{

    const SESSION_LOGIN = 'thissessionfirstlog';
    private $private_key;

    function setUp()
    {
        $this->weblogin = new Intraface_Weblogin(self::SESSION_LOGIN, new Fake_Auth_Intraface_Intranet());
    }

    function tearDown()
    {
        unset($this->weblogin);
    }

    function testConstructionOfWeblogin()
    {
        $this->assertTrue(is_object($this->weblogin));
    }
}
