<?php
class Intraface_modules_contact_ContactGateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function findFromId($id)
    {
        return new Contact($this->kernel, $id);
    }
}