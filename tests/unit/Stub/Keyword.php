<?php
class MyKeyword extends Keyword
{
    function __construct($object, $id = 0)
    {
        parent::__construct($object, $id);
    }
}

class FakeKeywordKeyword
{
    protected $id;
    protected $keyword;

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
    protected $id;
    protected $keyword;

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

    function identify()
    {
        return 'fakekeywordobject';
    }

    function getId()
    {
        return 1;
    }

    function getKernel()
    {
        return $this->kernel;
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

    function identify()
    {
        return 'fakekeywordobject';
    }

    function getId()
    {
        return 1;
    }

    function getKernel()
    {
        return $this->kernel;
    }
}

class MyStringKeyword extends Keyword
{
    function __construct($object, $id = 0)
    {
        parent::__construct($object, $id);
    }
}
