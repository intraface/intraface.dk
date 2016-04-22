<?php
class Intraface_XMLRPC_Contact_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Contact_Server'
    );
    protected $prefix = 'contact';
    protected $default_server_version = '0.1.0';
}
