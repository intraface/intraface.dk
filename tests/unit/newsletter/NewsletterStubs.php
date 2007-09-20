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

class FakeNewsletterKernel
{
    public $intranet;
    public $user;

    function useModule() {}
}

class FakeNewsletterIntranet
{
    public function get()
    {
        return 1;
    }
}

class FakeNewsletterUser
{
    public function get()
    {
        return 1;
    }
}

class FakeNewsletterAddress
{
    function get()
    {
        return 'lars@legestue.net';
    }
}

class FakeNewsletterContact
{
    public $address;

    function __construct()
    {
        $this->address = new FakeNewsletterAddress;
    }

    function get() {
        return 1;
    }

    function getLoginUrl() {
        return 'loginurl';
    }
}

class FakeNewsletterSubscriber
{
    function load() {}

    function get()
    {
        return 1;
    }

    function getContact()
    {
        return new FakeNewsletterContact;
    }
}
?>