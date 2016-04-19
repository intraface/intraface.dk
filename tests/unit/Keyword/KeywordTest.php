<?php
require_once 'Intraface/shared/keyword/Keyword.php';
require_once dirname(__FILE__) . '/../Stub/Keyword.php';

class KeywordTest extends PHPUnit_Framework_TestCase
{
    private $keyword;

    function setUp()
    {
        $this->keyword = $this->createKeyword();
        $db = MDB2::singleton(DB_DSN);
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
}
