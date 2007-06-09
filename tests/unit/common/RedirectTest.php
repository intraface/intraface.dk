<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Redirect.php';

class FakeRedirectUser
{
    function get()
    {
        return 1;
    }
}

class FakeRedirectKernel
{
    public $user;
    function __construct()
    {
        $this->user = new FakeRedirectUser();
    }
}

class RedirectTest extends PHPUnit_Framework_TestCase
{

    function testConstruction()
    {
        $redirect = new Redirect(new FakeRedirectKernel);
        $this->assertTrue(is_object($redirect));
    }

}
?>
