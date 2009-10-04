<?php
class Intraface_XMLRPC_Newsletter_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Newsletter_Server',
        '0.2.0' => 'Intraface_XMLRPC_Newsletter_Server0100'
    );

    protected $prefix = 'newsletter';

    protected $default_server_version = '0.2.0';

    /*
    function getServer()
    {
    	$options = array('prefix' => 'newsletter.', 'encoding' => 'utf-8');

        if ($this->query('version') != '') {
        	$server = $this->available_servers[$this->query('version')];
        } else {
        	$server = 'Intraface_XMLRPC_Newsletter_Server0100';
        }
        return XML_RPC2_Server::create(new $server(), $options);
    }
    */
}
