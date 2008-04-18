<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Kernel.php';

class FakeKernelIntranet
{
    function hasModuleAccess()
    {
        return true;
    }
}

class KernelTest extends PHPUnit_Framework_TestCase
{

    function testRandomKey()
    {
        $this->assertTrue(strlen(Kernel::randomKey(9)) == 9);
    }

    function testWebloginReturnsTrueOnValidLoginAndCreatesTheCorrectObjectsInsideKernel()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE intranet');

        $this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
        $this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
        $db->exec('TRUNCATE intranet');
        $db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));

        $session_id = 'somerandomsession';
        $kernel = new Kernel;
        $this->assertFalse($kernel->weblogin('private', 'wrongkey', $session_id));
        $this->assertTrue($kernel->weblogin('private', $this->private_key, $session_id));
        $this->assertEquals(get_class($kernel->weblogin), 'Weblogin');
        $this->assertEquals(get_class($kernel->intranet), 'Intranet');
        $this->assertEquals(get_class($kernel->setting), 'Setting');
        $this->assertTrue($kernel->weblogin('public', $this->public_key, $session_id));
        $this->assertEquals(get_class($kernel->weblogin), 'Weblogin');
        $this->assertEquals(get_class($kernel->intranet), 'Intranet');
        $this->assertEquals(get_class($kernel->setting), 'Setting');
    }

    function testWebloginMakesSureThatSessionIssetInsideKernelAsTheSameAsTheOneWebloginGetsWhenUsingWeblogin()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE intranet');

        $this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
        $this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
        $db->exec('TRUNCATE intranet');
        $db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));

        $session_id = 'somerandomsession';
        $kernel = new Kernel;
        $kernel->weblogin('private', $this->private_key, $session_id);
        $this->assertEquals($session_id, $kernel->getSessionId());
    }

    function testModuleThrowsAnExceptionWhenNoIntranetIsset()
    {
        $kernel = new Kernel;
        try {
            $kernel->module('intranetmaintenance');
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    function testModuleReturnsTheModuleAsAnObjectTrueWhenModuleIsAvailableAndSetsPrimaryModule()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeKernelIntranet;
        $this->assertFalse($kernel->getPrimaryModule());
        $this->assertTrue(is_object($kernel->module('intranetmaintenance')));
        $this->assertTrue(is_object($kernel->getPrimaryModule()));
    }


    function testUseModuleThrownAnExceptionWhenNoIntranetIsset()
    {
        $kernel = new Kernel;
        try {
            $kernel->useModule('intranetmaintenance');
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    function testUseModuleReturnsTheModuleAsAnObjectTrueWhenModuleIsAvailable()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeKernelIntranet;
        $this->assertTrue(is_object($kernel->useModule('intranetmaintenance')));
    }

    function testGetModule()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeKernelIntranet;
        $this->assertTrue(is_object($kernel->useModule('intranetmaintenance')));
        $this->assertTrue(is_object($kernel->getModule('intranetmaintenance')));
    }

    function testGetModules()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeKernelIntranet;
        $this->assertTrue(is_array($kernel->getModules()));
    }

}