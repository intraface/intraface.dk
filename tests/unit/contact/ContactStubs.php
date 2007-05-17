<?php
class FakeContact
{
    private $id = 1;

    public function get()
    {
        return $this->id;
    }
}

class FakeIntranet
{
    public function get()
    {
        return 1;
    }
}

class FakeKernel
{
    public $intranet;
}

?>