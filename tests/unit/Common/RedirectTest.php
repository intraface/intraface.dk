<?php
require_once 'Intraface/functions.php';

/**
 * Remember this should actually only be tests whether the extend functionality works.
 * The tests are in Intraface_3Party
 */
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

    function getSessionId() {
        return 'dfp323ewrjif2309f32f30f23vcjtjkjw';
    }
}

class RedirectTest extends PHPUnit_Framework_TestCase
{
    private $table = 'redirect';
    private $server_vars = array();
    private $get_vars = array();

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
        if (PEAR::isError($this->db)) {
            die($this->db->getUserInfo());
        }
        $result = $this->db->exec('TRUNCATE redirect');
        $result = $this->db->exec('TRUNCATE redirect_parameter');
        $result = $this->db->exec('TRUNCATE redirect_parameter_value');

        $_SERVER['SCRIPT_URI'] = 'http://example.php/from.php';
        $_SERVER['HTTP_HOST'] = 'http://example.php/';
        $_SERVER['REQUEST_URI'] = 'http://example.php/from.php';
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

    function testGoRedirectAndsetDestination()
    {
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $parameter_to_return_with = 'add_contact_id'; // activates the parameter sent back to the return page
        $this->assertEquals($destination_url . '?redirect_id=1', $url);
    }
/*
    function testRecieveRedirectAndGetRedirect()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $standard_page_without_redirect = 'standard.php';
        $this->assertEquals($return_url . '&return_redirect_id=1', $redirect->getRedirect($standard_page_without_redirect));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testReturnsRedirect()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $standard_page_without_redirect = 'standard.php';
        $this->assertEquals($return_url . '&return_redirect_id=1', $redirect->getRedirect($standard_page_without_redirect));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // returning
        $_GET['return_redirect_id'] = 1;
        $redirect = Intraface_Redirect::returns($kernel);
        $this->assertEquals(1, $redirect->getId());
    }

    function testLoadingARedirect()
    {
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        $redirect = new Intraface_Redirect($kernel, 1);
        $this->assertEquals(1, $redirect->id);
    }

    function testSetIdentifierBeforeSetDestination() {
        $redirect = $this->createRedirect();
        $this->assertTrue($redirect->setIdentifier('identifier1'));

    }

    function testSetIdentifierAfterSetDestination() {
        $redirect = $this->createRedirect();
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $redirect->setDestination($destination_url, $return_url);
        $this->assertTrue($redirect->setIdentifier('identifier1'));
    }


    function testThisUri() {
        $_SERVER['HTTPS']       = 'https://example.dk/index.php';
        $_SERVER['HTTP_HOST']   = 'example.dk';
        $_SERVER['REQUEST_URI'] = '/index.php';

        $redirect = $this->createRedirect();
        $this->assertEquals('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $redirect->thisUri());
        unset($_SERVER['HTTPS']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
    }

    function testDeleteWithNoIdReturnsTrue()
    {
        $redirect = $this->createRedirect();
        $this->assertTrue($redirect->delete());
    }

    function testDeleteExistingRedirectReturnsTrue()
    {
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $this->assertTrue($redirect->delete());
    }

    function testAskParameter()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $this->assertTrue($redirect->askParameter('param'));
    }

    function testSetParameterWithValidParameter()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $redirect->askParameter('param');

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertTrue($redirect->setParameter('param', 120));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testSetParameterWithInvalidParameter()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $redirect->askParameter('param');

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertFalse($redirect->setParameter('wrong_param', 120));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testIsMultipleParameter() {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $this->assertTrue($redirect->askParameter('param', 'multiple'));

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertTrue($redirect->isMultipleParameter('param') > 0);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testReturnFromRedirectWithSingleParameter()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $redirect->askParameter('param');

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $redirect->setParameter('param', 120);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);


        // returning
        $_GET['return_redirect_id']     = 1;
        $redirect = Intraface_Redirect::returns($kernel);
        // notice that the returned format is string despite that the given is integer.
        $this->assertEquals('120', $redirect->getParameter('param'));
    }

    function testReturnFromRedirectWithMultiParameter()
    {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);
        $redirect->askParameter('param', 'multiple');

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $redirect->setParameter('param', 120);
        $redirect->setParameter('param', 140);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // returning
        $_GET['return_redirect_id']     = 1;
        $redirect = Intraface_Redirect::returns($kernel);
        // print_r($redirect->getParameter('param'));
        $this->assertEquals(array(120, 140), $redirect->getParameter('param'));
    }

    function testGetIdentifier() {
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $redirect->setIdentifier('identifier1');
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals('identifier1', $redirect->getIdentifier('identifier1'));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testGetId() {
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals(1, $redirect->getId());
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testGetRedirectQueryString() {
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk/';
        $_SERVER['REQUEST_URI']  = 'state.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals('redirect_id=1', $redirect->getRedirectQueryString());
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testLoadRedirectAfterSubmit() {

        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // print_r($this->server_vars);
        // die;

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // recieve after submit to same page
        $_SERVER['HTTP_REFERER'] = $destination_url;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals(1, $redirect->getId());

    }

    function testLoadRedirectAfterLoadFromAnotherPage() {

        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // recieve after refer from another page
        $_SERVER['HTTP_REFERER'] = 'http://example.dk/another_page.php';
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals(0, $redirect->getId());

    }


    function testLoadRedirectAfterLoadFromAnotherPageAndThenFromTheSamePage() {

        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $url = $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // recieve after refer from another page
        $_SERVER['HTTP_REFERER'] = 'http://example.dk/another_page.php';
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $redirect = Intraface_Redirect::receive($kernel);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);

        // and the recieve after the same page again
        // recieve after submit to same page
        $_SERVER['HTTP_REFERER'] = $destination_url;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals(0, $redirect->getId());
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
    }

    function testLoadRedirectWithSecondRedirectInBetween() {

        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $return_url      = 'http://example.dk/state.php?id=1';
        $destination_url = 'http://example.dk/page.php';
        $redirect->setDestination($destination_url, $return_url);

        // receiving
        $_SERVER['HTTP_REFERER'] = $return_url;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals(1, $redirect->getId());
        $redirect_two = Intraface_Redirect::go($kernel);
        $return_url_two      = 'http://example.dk/page.php';
        $destination_url_two = 'http://example.dk/add_page.php';
        $url = $redirect_two->setDestination($destination_url_two, $return_url_two.'?'.$redirect->getRedirectQueryString());
        $this->assertEquals($destination_url_two.'?redirect_id=2', $url);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // second recieve
        $_SERVER['HTTP_REFERER'] = $return_url_two;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/add_page.php';
        $_GET['redirect_id']     = 2;
        $redirect = Intraface_Redirect::receive($kernel);
        $default = 'http://example.dk/another_page.php';
        $this->assertEquals($return_url_two.'?redirect_id=1&return_redirect_id=2', $redirect->getRedirect($default));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
    }

    function testLoadRedirectAfterSecondRedirectAndSubmit() {
        // go
        $kernel = new FakeRedirectKernel;
        $redirect = Intraface_Redirect::go($kernel);
        $url1      = 'http://example.dk/state.php?id=1';
        $url2 = 'http://example.dk/page.php';
        $redirect->setDestination($url2, $url1);

        // receiving
        $_SERVER['HTTP_REFERER'] = $url1;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $_GET['redirect_id']     = 1;
        $redirect = Intraface_Redirect::receive($kernel);
        $redirect_two = Intraface_Redirect::go($kernel);
        $url3 = 'http://example.dk/add_page.php';
        $redirect_two->setDestination($url3, $url2.'?'.$redirect->getRedirectQueryString());
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // second recieve
        $_SERVER['HTTP_REFERER'] = $url3;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/add_page.php';
        $_GET['redirect_id']     = 2;
        $redirect = Intraface_Redirect::receive($kernel);
        $default = 'http://example.dk/another_page.php';
        $this->assertEquals($url2.'?redirect_id=1&return_redirect_id=2', $redirect->getRedirect($default));
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);

        // receiving on first page again
        $_SERVER['HTTP_REFERER'] = $url3;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $_GET['redirect_id']     = 1;
        $_GET['return_redirect_id']     = 2;
        $redirect = Intraface_Redirect::receive($kernel);
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);
        unset($_GET['redirect_id']);
        unset($_GET['return_redirect_id']);

        // return after submit
        $_SERVER['HTTP_REFERER'] = $url2;
        $_SERVER['HTTP_HOST']    = 'example.dk';
        $_SERVER['REQUEST_URI']  = '/page.php';
        $redirect = Intraface_Redirect::receive($kernel);
        $this->assertEquals(1, $redirect->getId());
        unset($_SERVER['HTTP_REFERER']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['REQUEST_URI']);

    }
*/
    ////////////////////////////////////////////////////////////////////

    function createRedirect()
    {
        return new Intraface_Redirect(new FakeRedirectKernel);
    }

    function getVarsFromUrl($url) {

        $parts = explode('?');
        if (!isset($parts[1])) {
            return array();
        }
        $params = explode('&', $parts[1]);
        if (!is_array($params)) {
            return array();
        }

        $param = array();
        foreach ($params AS $p) {
            $parts = explode('=', $p);
            if (is_array($parts) && count($parts) == 2) {
                $param[$parts[0]] = $parts[1];
            }
        }
        return $param;

    }
}
