<?php
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
