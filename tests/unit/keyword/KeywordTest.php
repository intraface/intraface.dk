<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/shared/keyword/Keyword.php';

class FakeKeywordIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeKeywordKernel
{
    public $intranet;

    function __construct()
    {
        $this->intranet = new FakeKeywordIntranet;
    }

    function useModule()
    {
        return true;
    }
}

class FakeKeywordObject
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new FakeKeywordKernel;
    }

    function get()
    {
        return 1;
    }
}

class MyKeyword extends Keyword
{
    function __construct($object, $id = 0)
    {
        $this->registerType(1, 'cms');
        $this->registerType(2, 'contact');
        parent::__construct($object, $id);
    }
}

class FakeKeywordKeyword
{
    public $id;
    public $keyword;

    function __construct($id = 1, $keyword = 'test')
    {
        $this->id = $id;
        $this->keyword = $keyword;
    }

    function getId()
    {
        return $this->id;
    }

    function getKeyword()
    {
        return $this->keyword;
    }
}

class KeywordTest extends PHPUnit_Framework_TestCase
{
    private $keyword;

    function setUp()
    {
        $this->keyword = $this->createKeyword();
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE keyword');
        $db->query('TRUNCATE keyword_x_object');
    }

    function saveKeyword($keyword = 'test')
    {
        $data = array('keyword' => $keyword);
        return $this->keyword->save($data);
    }

    function createKeyword($id = 0)
    {
        return new MyKeyword(new FakeKeywordObject, $id);
    }

    //////////////////////////////////////////////////////

    function testCreatesAUsableKeyword()
    {
        $this->assertTrue(is_object($this->keyword));
    }

    function testSaveAnEmptyArrayCreatesNoUndefinedIndexesAndReturnsFalseBecauseNoKeywordHasBeenSupplied()
    {
        $data = array();
        $this->assertFalse($this->keyword->save($data));
    }

    function testSaveReturnsAnInteger()
    {
        $data = array('keyword' => 'test');
        $this->assertTrue($this->keyword->save($data) > 0);
    }

    function testKeywordHasBeenPersistedAndCanBeFoundAgain()
    {
        $id = $this->saveKeyword();
        $keyword = $this->createKeyword($id);
        $this->assertEquals(1, $keyword->getId());
        $this->assertEquals('test', $keyword->getKeyword());
    }

    /*
    function testFactory()
    {
        $id = $this->saveKeyword();
        $keyword = Keyword::factory(new FakeKeywordKernel, $id);
        $this->assertTrue(is_object($keyword));
        $this->assertEquals(1, $keyword->getId());
        $this->assertEquals('test', $keyword->getKeyword());
    }
    */

    function testDeleteReturnsTrueAndActuallyDeletesAKeyword()
    {
        $id = $this->saveKeyword();
        $keyword = $this->createKeyword($id);
        $this->assertTrue($keyword->delete());
    }

    function testGetAllKeywords()
    {
        $id = $this->saveKeyword();
        $keywords = $this->keyword->getAllKeywords();
        $this->assertEquals(1, $keywords[0]['id']);
        $this->assertEquals('test', $keywords[0]['keyword']);
    }

    function testRegisterTypeAndGetType()
    {
        $this->keyword->registerType(1, 'cms');
        $this->assertEquals(1, $this->keyword->getTypeKey('cms'));
    }

}

