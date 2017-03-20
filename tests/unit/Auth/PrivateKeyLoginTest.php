<?php
class PrivateKeyLoginTest extends PHPUnit_Framework_TestCase
{

    const SESSION_LOGIN = 'thissessionfirstlog';
    private $db;
    private $adapter;

    function setUp()
    {
        $private_key = md5('private' . date('d-m-Y H:i:s') . 'test');

        $this->db = MDB2::singleton(DB_DSN);
        $this->db->exec('TRUNCATE intranet');
        $this->db->exec('INSERT INTO intranet SET private_key = ' . $this->db->quote($private_key, 'text'));

        $this->adapter = new Intraface_Auth_PrivateKeyLogin($this->db, self::SESSION_LOGIN, $private_key);
    }

    function tearDown()
    {
        unset($this->db);
        unset($this->adapter);
    }

    function testConstructionOfAdapter()
    {
        $this->assertTrue(is_object($this->adapter));
    }

    function testAuthWithWrongPrivateKey()
    {
        $adapter = new Intraface_Auth_PrivateKeyLogin($this->db, self::SESSION_LOGIN, 'wrongprivatekey');
        $this->assertFalse($adapter->auth());
    }

    function testAuthWithCorrectPrivateKey()
    {
        $this->assertTrue(is_object($this->adapter->auth()));
    }
}
