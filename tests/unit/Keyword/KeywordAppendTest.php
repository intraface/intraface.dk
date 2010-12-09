<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/shared/keyword/Keyword.php';
require_once dirname(__FILE__) . '/../Stub/Keyword.php';

class KeywordAppendTest extends PHPUnit_Framework_TestCase
{
    /////////////////////////////////////////////////////////////

    function setUp()
    {
        $this->keyword = new Intraface_Keyword_Appender(new FakeKeywordAppendObject);
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->query('TRUNCATE keyword');
        $this->db->query('TRUNCATE keyword_x_object');
    }

    function createKeyword($keyword = 'test')
    {
        $k = new Keyword(new FakeKeywordAppendObject());
        $result = $k->save(array('keyword' => $keyword));
        $this->assertTrue($result > 0);
        return $k;
    }

    ///////////////////////////////////////////////////////////////

    function testAddKeyword()
    {
        $this->assertTrue($this->keyword->addKeyword($this->createKeyword()));
        $keywords = $this->keyword->getConnectedKeywords();
        $this->assertEquals(1, $keywords[0]['id']);
        $this->assertEquals('test', $keywords[0]['keyword']);
    }

    function testAddKeywords()
    {
        $keyword = $this->createKeyword('test');
        $keyword2 = $this->createKeyword('test 2');
        $keywords = array($keyword, $keyword2);
        $this->assertTrue($this->keyword->addKeywords($keywords));
        $keywords_connected = $this->keyword->getConnectedKeywords();
        $this->assertEquals(2, count($keywords_connected));
    }

    function testGetConnectedKeywords()
    {
        $this->keyword->addKeyword($this->createKeyword());
        $keywords = $this->keyword->getConnectedKeywords();
        $this->assertEquals(1, $keywords[0]['id']);
        $this->assertEquals('test', $keywords[0]['keyword']);
    }

    function testGetUsedKeywords()
    {
        $this->keyword->addKeyword($this->createKeyword());
        $keywords = $this->keyword->getUsedKeywords();
        $this->assertEquals(1, $keywords[0]['id']);
        $this->assertEquals('test', $keywords[0]['keyword']);
    }

    function testDeleteConnectedKeywords()
    {
        $this->keyword->addKeyword($this->createKeyword());
        $this->assertTrue($this->keyword->deleteConnectedKeywords());
        $keywords = $this->keyword->getConnectedKeywords();
        $this->assertTrue(empty($keywords));
    }

    function testGetConnectedKeywordsAsString()
    {
        $this->keyword->addKeyword($this->createKeyword());
        $keyword2 = $this->createKeyword('test 2');
        $this->keyword->addKeyword($keyword2);
        $string = $this->keyword->getConnectedKeywordsAsString();
        $this->assertEquals('test, test 2', $string);
    }
}
