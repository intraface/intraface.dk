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
    public $address;
    
    public function get()
    {
        return 1;
    }
}

class FakeContactKernel
{
    public $intranet;
    public $setting;

    function getModule()
    {
        return new FakeContactModule;
    }
    
    function useModule()
    {
        return new FakeContactModule;
    }
    
    function useShared($shared)
    {
        switch($shared) {
            case 'email':
                require_once 'Intraface/shared/email/Email.php';
                break;
        }
    }
}

class FakeContactSetting
{
    function get()
    {
        return 'test';
    }
}

class FakeContactModule
{
    function getSetting()
    {

    }
}