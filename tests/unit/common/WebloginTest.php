<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Weblogin.php';

class WebloginTest extends PHPUnit_Framework_TestCase {

    const SESSION_LOGIN = 'thissessionfirstlog';
    private $private_key;

    function setUp() {
        
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE intranet');
        
        $this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
        $this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
        $db->exec('TRUNCATE intranet');
        $db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));
    }

    function testConstructionOfWeblogin() {
        $weblogin = new Weblogin(self::SESSION_LOGIN);
        $this->assertTrue(is_object($weblogin));
    }

    function testAuthWithWrongPrivateKey() {
        $weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
        $this->assertFalse($weblogin->auth('private', 'wrongkey'));
    }

    function testAuthWithWrongPublicKey() {
        $weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
        $this->assertFalse($weblogin->auth('public', 'wrongkey'));
    }

    function testAuthWithCorrectPrivateKey() {
        $weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
        $this->assertTrue(($weblogin->auth('private', $this->private_key) > 0));

    }

    function testAuthWithCorrectPublicKey() {
        $weblogin = new Weblogin('sessidfsdfdsfdsfdsf');
        $this->assertTrue(($weblogin->auth('public', $this->public_key) > 0));

    }
}