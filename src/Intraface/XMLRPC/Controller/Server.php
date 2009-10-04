<?php
class Intraface_XMLRPC_Controller_Server extends k_Component
{
    protected $available_servers = array();
    protected $server;
    protected $backends = array(
        'php' => 'utf-8',
        'xmlrpcext' => 'iso-8859-1');
    protected $prefix;

    /*
    function getServer()
    {
        if ($this->query('version') != '') {
            if (isset($this->available_servers[$this->query('version')])) {
                $server = $this->available_servers[$this->query('version')];
            } else {
                throw new Exception('Invalid server version');
            }
        } else {
            if (isset($this->available_servers[$this->default_server_version])) {
                $server = $this->available_servers[$this->default_server_version];
            } else {
                throw new Exception('Invalid default server version');
            }

        }

        if ($this->query('backend') != '') {
            if (in_array($this->query('backend'), array('php', 'xmlrpcext'))) {
                $backend = $this->query('backend');
            } else {
                throw new Exception('Invalid backend. Must be php or xmlrpcext');
            }
        } else {
            $backend = 'xmlrpcext';
        }

        if (!isset($this->prefix)) {
            throw new Exception('You need to set $this->prefix in class');
        }

        $this->encoding = $this->backends[$backend];

        $options = array(
            'prefix' => $this->prefix,
            'encoding' => $this->encoding,
            'backend' => $backend);

        return XML_RPC2_Server::create(new $server($this->encoding), $options);
    }
    */

    function renderHtml()
    {
        ob_start();
        $this->getServer()->autoDocument();
        $result = ob_get_clean();

        return $result;
    }

    function renderXml() {

        return $this->getServer()->getResponse();
    }

    function POST()
    {
        return $this->getResponse();
    }

    function getResponse()
    {
        return $this->getServer()->getResponse();
    }
}
