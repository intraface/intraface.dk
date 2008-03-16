<?php
class FakeNewsletterList
{
    public $kernel;

    function __construct()
    {
        $this->kernel = new FakeNewsletterKernel();
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
        return new FakeNewsletterIntranet();
    }
}

class FakeNewsletterKernel
{
    public $intranet;
    public $user;
    public $setting;

    function __construct()
    {
        $this->intranet = new FakeNewsletterIntranet();
        $this->setting = new FakeNewsletterSettting;
    }

    function useModule() {}
    function getSessionId() {}
}

class FakeNewsletterSettting
{
    function get() {}
}

class FakeNewsletterIntranet
{
    public function get()
    {
        return 1;
    }

    function getId() {
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