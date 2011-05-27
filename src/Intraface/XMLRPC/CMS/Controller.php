<?php
class Intraface_XMLRPC_CMS_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.3.0' => 'Intraface_XMLRPC_CMS_Server0300',
        '0.4.0' => 'Intraface_XMLRPC_CMS_Server0400',
    );
    protected $prefix = 'cms';
    protected $default_server_version = '0.4.0';
}
