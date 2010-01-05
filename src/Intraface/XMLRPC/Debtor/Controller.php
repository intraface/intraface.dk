<?php
class Intraface_XMLRPC_Debtor_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Debtor_Server'
    );

    protected $prefix = 'debtor';

    protected $default_server_version = '0.1.0';
}
