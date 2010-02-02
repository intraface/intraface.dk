<?php
class Intraface_GatewayFactory
{
    protected $mdb2;
    protected $db;
    protected $kernel;

    function __construct(MDB2_Driver_Common $mdb2, DB_Sql $db, Intraface_KernelGateway $kernel)
    {
        $this->mdb2;
        $this->db = $db;
        $this->kernel = $kernel;
    }

    function new_Intraface_modules_newsletter_SubscribersGateway($user)
    {

    }
}
