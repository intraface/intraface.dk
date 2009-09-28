<?php
class Intraface_XMLRPC_CMS_Controller extends Intraface_XMLRPC_Controller_Server
{
    function getServer()
    {
        $options = array('prefix' => 'cms.',
                 'encoding' => 'utf-8');

        return XML_RPC2_Server::create(new Intraface_XMLRPC_CMS_Server0300(), $options);
    }
}
