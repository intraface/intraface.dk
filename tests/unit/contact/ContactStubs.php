<?php
class FakeContactContact
{
    private $id = 1;
    public $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function get()
    {
        return $this->id;
    }

}

class FakeContactIntranet
{
    public function get()
    {
        return 1;
    }
}

class FakeContactKernel
{
    public $intranet;

    function getModule()
    {
        return new FakeContactModule;
    }
}

class FakeContactModule
{
    function getSetting()
    {

    }
}