<?php
class Intraface_XMLRPC_Contact_Controller extends Intraface_XMLRPC_Controller_Server
{
    protected $available_servers = array(
        '0001' => 'Intraface_XMLRPC_Contact_Server'
    );

    function getServer()
    {
    	$options = array('prefix' => 'contact.', 'encoding' => 'iso-8859-1');

        if (!empty($this->GET['version'])) {
        	$server = $this->available_servers[$this->GET['version']];
        } else {
        	$server = 'Intraface_XMLRPC_Contact_Server';
        }

        return XML_RPC2_Server::create(new Intraface_XMLRPC_Contact_Server(), $options);
    }
}
