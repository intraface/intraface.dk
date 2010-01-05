<?php
class Intraface_XMLRPC_OnlinePayment_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.2.0' => 'Intraface_XMLRPC_OnlinePayment_Server0002'
    );

    protected $prefix = 'newsletter';

    protected $default_server_version = '0.2.0';
}
