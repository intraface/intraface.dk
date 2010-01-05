<?php
class Intraface_XMLRPC_Shop_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Shop_Server',
        '0.2.0' => 'Intraface_XMLRPC_Shop_Server2',
        '0.4.0' => 'Intraface_XMLRPC_Shop_Server0004'
    );

    protected $prefix = 'shop';

    protected $default_server_version = '0.4.0';
}
