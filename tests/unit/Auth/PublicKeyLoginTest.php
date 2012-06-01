<?php
class PublicKeyLoginTest extends PHPUnit_Framework_TestCase {

    const SESSION_LOGIN = 'thissessionfirstlog';
    private $adapter;
    private $db;

    function setUp()
    {
        $public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
        $this->db = MDB2::singleton(DB_DSN);
        $this->db->exec('TRUNCATE intranet');
        $this->db->exec('INSERT INTO intranet SET public_key = ' . $this->db->quote($public_key, 'text'));
        $this->adapter = new Intraface_Auth_PublicKeyLogin($this->db, self::SESSION_LOGIN, $public_key);
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

    function testAuthWithWrongPublicKey()
    {
        $adapter = new Intraface_Auth_PublicKeyLogin($this->db, self::SESSION_LOGIN, 'wrongprivatekey');
        $this->assertFalse($adapter->auth());
    }

    function testAuthWithCorrectPublicKey()
    {
        $this->assertTrue(is_object($this->adapter->auth()));
    }
}
