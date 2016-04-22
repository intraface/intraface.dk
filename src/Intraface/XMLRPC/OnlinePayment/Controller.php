<?php
class Intraface_XMLRPC_OnlinePayment_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.2.0' => 'Intraface_XMLRPC_OnlinePayment_Server0002',
        '0.3.0' => 'Intraface_XMLRPC_OnlinePayment_Server0100'
    );

    protected $prefix = 'newsletter';

    protected $default_server_version = '0.3.0';
    protected $doctrine;

    function __construct(Doctrine_Connection_Common $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    function getServer()
    {
        if ($this->getVersion() == '0.3.0') {
            return XML_RPC2_Server::create(new Intraface_XMLRPC_OnlinePayment_Server0100($this->doctrine, $this->getEncoding()), $this->getServerOptions());
        }

        return parent::getServer();
    }
}
