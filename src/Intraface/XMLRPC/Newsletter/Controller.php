<?php
class Intraface_XMLRPC_Newsletter_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Newsletter_Server',
        '0.2.0' => 'Intraface_XMLRPC_Newsletter_Server0100'
    );
    protected $prefix = 'newsletter';
    protected $default_server_version = '0.2.0';
}
