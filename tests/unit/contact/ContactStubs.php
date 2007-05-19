<?php
class FakeContactContact
{
    private $id = 1;

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
}

?>