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

class FakeRedirectIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeRedirectKernel
{
    public $user;
    public $intranet;
    function __construct()
    {
        $this->user = new FakeRedirectUser();
        $this->intranet = new FakeRedirectIntranet();
    }
}

class RedirectTest extends PHPUnit_Framework_TestCase
{
    private $table = 'redirect';

    function setUp()
    {
        $this->db = MDB2::factory(DB_DSN);
        if (PEAR::isError($this->db)) {
            die($this->db->getUserInfo());
        }
        $result = $this->db->exec('TRUNCATE ' . $this->table);
    }

    function tearDown()
    {
        $result = $this->db->exec('TRUNCATE ' . $this->table);
    }

    function testConstruction()
    {
        $redirect = $this->createRedirect();
        $this->assertTrue(is_object($redirect));
    }

    function testGoRedirect()
    {
        $kernel = new FakeRedirectKernel;
        $redirect = Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $parameter_to_return_with = 'add_contact_id'; // activates the parameter sent back to the return page
        $this->assertEquals($destination_url . '?redirect_id=1', $url);
    }

    function testRecieveRedirect()
    {
    // TODO Saa vidt jeg kan see har vi ingen mulighed for at fake redirect som
    // det ser ud nu, fordi det hele afhaenger af globale variable. Det skal vi have lavet om.
    // men kun med nogle gode tests forst naturligvis.

        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['SCRIPT_NAME']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Redirect::receive($kernel);
        $standard_page_without_redirect = 'standard.php';
        $this->assertEquals($return_url . '&return_redirect_id=1', $redirect->getRedirect($standard_page_without_redirect));
    }

    function testDeleteWithNoIdReturnsTrue()
    {
        $redirect = $this->createRedirect();
        $this->assertTrue($redirect->delete());
    }

    function testDeleteExistingRedirectReturnsTrue()
    {
        $kernel = new FakeRedirectKernel;
        $redirect = Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $this->assertTrue($redirect->delete());
    }

    function testLoadingARedirect()
    {
        $kernel = new FakeRedirectKernel;
        $redirect = Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        $redirect = new Redirect($kernel, 1);
        $this->assertEquals(1, $redirect->id);
        $this->assertEquals($return_url, $redirect->get('return_url'));
    }

    ////////////////////////////////////////////////////////////////////

    function createRedirect()
    {
        return new Redirect(new FakeRedirectKernel);
    }

}
?>
