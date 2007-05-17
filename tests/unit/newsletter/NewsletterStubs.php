<?php
class FakeNewsletterList
{
    public $kernel;

    function get($key)
    {
        switch ($key) {
            case 'reply_email':
                return 'test@legestue.net';
                break;
            default:
                return 1;
                break;
        }


    }
}

class FakeKernel
{
    public $intranet;
    public $user;

    function useModule() {}
}

class FakeIntranet
{
    public function get()
    {
        return 1;
    }
}

class FakeUser
{
    public function get()
    {
        return 1;
    }
}

class FakeAddress
{
    function get()
    {
        return 'lars@legestue.net';
    }
}

class FakeContact
{
    public $address;

    function __construct()
    {
        $this->address = new FakeAddress;
    }

    function get() {
        return 1;
    }
}

class FakeSubscriber
{
    function load() {}

    function get()
    {
        return 1;
    }

    function getContact()
    {
        return new FakeContact;
    }
}
?>