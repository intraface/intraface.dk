<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/shared/keyword/Keyword.php';

if (!class_exists('FakeKeywordObject')) {
    class FakeKeywordObject
    {
        public $kernel;

        function __construct()
        {
            $this->kernel = new Stub_Kernel;
        }

        function get()
        {
            return 1;
        }
    }
}

if (!class_exists('FakeKeywordAppendObject')) {
    class FakeKeywordAppendObject
    {
        public $kernel;

        function __construct()
        {
            $this->kernel = new Stub_Kernel;
        }

        function get()
        {
            return 1;
        }
    }
}

class MyStringKeyword extends Keyword
{
    function __construct($object, $id = 0)
    {
        $this->registerType(1, 'cms');
        $this->registerType(2, 'contact');
        parent::__construct($object, $id);
    }
}

class FakeKeywordStringAppendKeyword
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


class KeywordStringAppendTest extends PHPUnit_Framework_TestCase
{
    /////////////////////////////////////////////////////////////

    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
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
