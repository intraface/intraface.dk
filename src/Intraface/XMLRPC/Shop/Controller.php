<?php
class Intraface_XMLRPC_Shop_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Shop_Server',
        '0.2.0' => 'Intraface_XMLRPC_Shop_Server2',
        '0.4.0' => 'Intraface_XMLRPC_Shop_Server0004',
        '0.5.0' => 'Intraface_XMLRPC_Shop_Server0100'
    );

    protected $prefix = 'shop';

    protected $default_server_version = '0.5.0';
    protected $doctrine;

    function __construct(Doctrine_Connection_Common $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    function getServer()
    {
        if ($this->getVersion() == '0.5.0') {
            return XML_RPC2_Server::create(new Intraface_XMLRPC_Shop_Server0100($this->doctrine, $this->getEncoding()), $this->getServerOptions());
        }

        return parent::getServer();
    }
}
