<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/shared/keyword/Keyword.php';

class FakeKeywordAppendIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeKeywordAppendKernel
{
    public $intranet;

    function __construct()
    {
        $this->intranet = new FakeKeywordAppendIntranet;
    }

    function useModule()
    {
        return true;
    }
}

class FakeKeywordAppendObject
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new FakeKeywordAppendKernel;
    }

    function get()
    {
        return 1;
    }
}

class FakeKeywordAppendKeyword
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


class KeywordAppendTest extends PHPUnit_Framework_TestCase
{
    /////////////////////////////////////////////////////////////

    function setUp()
    {
        $this->keyword = new Intraface_Keyword_Appender(new FakeKeywordAppendObject);
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE keyword');
        $db->query('TRUNCATE keyword_x_object');
        $res = $db->query('INSERT into keyword SET id = 1, keyword = "test", intranet_id = 1, type="contact"');
        $res = $db->query('INSERT into keyword SET id = 2, keyword = "test 2", intranet_id = 1, type="contact"');
    }

    function createKeyword($id = '1', $keyword = 'test')
    {
        return new FakeKeywordAppendKeyword($id, $keyword);
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
        $keyword = $this->createKeyword();
        $keyword2 = $this->createKeyword(2, 'test 2');
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
        $keyword2 = $this->createKeyword(2, 'test 2');
        $this->keyword->addKeyword($keyword2);
        $string = $this->keyword->getConnectedKeywordsAsString();
        $this->assertEquals('test, test 2', $string);
    }


    /*
    function testAddKeywordsByString()
    {
        $this->assertTrue($this->keyword->addKeywordsByString('tester, test'));
        $string = $this->keyword->getConnectedKeywordsAsString();
        $this->assertEquals('test, tester', $string);
    }


    */
}
