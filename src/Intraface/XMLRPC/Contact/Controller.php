<?php
class Intraface_XMLRPC_Contact_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0.1.0' => 'Intraface_XMLRPC_Contact_Server'
    );
    protected $prefix = 'contact';

    protected $default_server_version = '0.1.0';

    /*
    function getServer()
    {
    	$options = array('prefix' => 'contact.', 'encoding' => 'utf-8');

        if (!empty($this->GET['version'])) {
        	$server = $this->available_servers[$this->GET['version']];
        } else {
        	$server = 'Intraface_XMLRPC_Contact_Server';
        }

        return XML_RPC2_Server::create(new $server(), $options);
    }
    */
}
