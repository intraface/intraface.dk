<?php
class Intraface_XMLRPC_Admin_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Admin_Server'
    );
    protected $prefix = 'admin';

    protected $default_server_version = '0.1.0';
}
