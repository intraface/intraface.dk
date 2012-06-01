<?php
require_once 'Intraface/shared/keyword/Keyword.php';
require_once dirname(__FILE__) . '/../Stub/Keyword.php';

class KeywordStringAppendTest extends PHPUnit_Framework_TestCase
{
    /////////////////////////////////////////////////////////////

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE keyword');
        $db->query('TRUNCATE keyword_x_object');
    }

    function createKeyword()
    {
        return new MyStringKeyword(new FakeKeywordObject);
    }

    function createAppender()
    {
        return new Intraface_Keyword_Appender(new FakeKeywordAppendObject);
    }

    function createStringAppender()
    {
        return new Intraface_Keyword_StringAppender($this->createKeyword(), $this->createAppender());
    }

    ///////////////////////////////////////////////////////////////

    function testAddKeywordsByString()
    {

        $appender = $this->createStringAppender();
        $this->assertTrue($appender->addKeywordsByString('test, tester'));

        $keyword_appender = $this->createAppender();
        $keywords = $keyword_appender->getConnectedKeywords();
        $this->assertEquals(2, count($keywords));
    }

    function testAddKeywordsByStringCanAddTheSameKeywordsTwiceInARow()
    {
        $keywords_to_add = 'test, tester';

        $appender = $this->createStringAppender();
        $this->assertTrue($appender->addKeywordsByString($keywords_to_add));

        $keyword_appender = $this->createAppender();
        $keywords = $keyword_appender->getConnectedKeywords();
        $this->assertEquals(2, count($keywords));

        $this->assertTrue($appender->addKeywordsByString($keywords_to_add));

        $keyword_appender = $this->createAppender();
        $keywords = $keyword_appender->getConnectedKeywordsAsString();

        $this->assertEquals($keywords, $keywords_to_add);
    }
}
