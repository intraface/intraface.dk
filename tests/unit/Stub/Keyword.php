<?php
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

class MyStringKeyword extends Keyword
{
    function __construct($object, $id = 0)
    {
        $this->registerType(1, 'cms');
        $this->registerType(2, 'contact');
        parent::__construct($object, $id);
    }
}