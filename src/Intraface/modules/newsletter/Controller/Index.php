<?php
class Intraface_modules_newsletter_Controller_Index extends k_Component
{
    protected $kernel_gateway;
    protected $user_gateway;

    function __construct(Intraface_KernelGateway $gateway, Intraface_UserGateway $user_gateway)
    {
        $this->kernel_gateway = $gateway;
        $this->user_gateway = $user_gateway;
    }

    function map($name)
    {
        if ($name == 'lists') {
            return 'Intraface_modules_newsletter_Controller_Lists';
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('lists'));
    }

    function getKernel()
    {
        return $this->kernel_gateway->findByUserobject($this->user_gateway->findByUsername($this->identity()->user()));
    }
}