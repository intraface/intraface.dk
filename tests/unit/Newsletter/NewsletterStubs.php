<?php
class FakeNewsletterList
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new Stub_Kernel();
    }

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

    function getIntranet()
    {
        return new Stub_Intranet();
    }
}

class FakeNewsletterContact
{
    public $address;

    function __construct()
    {
        $this->address = new Stub_Address;
    }

    function get()
    {
        return 1;
    }

    function getLoginUrl()
    {
        return 'loginurl';
    }
}

class FakeNewsletterSubscriber
{
    function load()
    {
    }

    function get()
    {
        return 1;
    }

    function getContact()
    {
        return new FakeNewsletterContact;
    }
}
