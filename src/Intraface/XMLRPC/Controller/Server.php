<?php
class Intraface_XMLRPC_Controller_Server extends k_Component
{
    protected $available_servers = array();
    protected $backends = array(
        'php' => 'utf-8',
        'xmlrpcext' => 'iso-8859-1');

    protected $prefix;
    protected $backend = 'php';
    protected $default_server_version = null;

    protected function getBackend()
    {
        if ($this->query('backend') != '') {
            if (in_array($this->query('backend'), array('php', 'xmlrpcext'))) {
                $backend = $this->query('backend');
            } else {
                throw new Exception('Invalid backend. Must be php or xmlrpcext');
            }
        } else {
            $backend = 'xmlrpcext';
        }
        return $backend;
    }

    function getEncoding()
    {
        return $this->encoding = $this->backends[$this->getBackend()];
    }

    protected function getServerOptions()
    {

        if (!isset($this->prefix)) {
            throw new Exception('You need to set $this->prefix in class');
        }

        $options = array(
            'prefix' => $this->prefix . '.',
            'encoding' => $this->getEncoding(), // $this->encoding
            'backend' => $this->getBackend());

        return $options;
    }

    function getVersion()
    {
        if ($this->query('version') != '') {
            if (isset($this->available_servers[$this->query('version')])) {
                return $this->query('version');
            } else {
                throw new Exception('Invalid server version');
            }
        } else {
            if (isset($this->available_servers[$this->default_server_version])) {
                return $this->default_server_version;
            } else {
                throw new Exception('Invalid default server version');
            }
        }
    }

    protected function getServer()
    {
        /*
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
        */
        $server = $this->available_servers[$this->getVersion()];

        return XML_RPC2_Server::create(new $server($this->getEncoding()), $this->getServerOptions());
    }

    function dispatch()
    {
        switch ($this->query('backend')) {
            case 'xmlrpcext':
                // @todo tests does not pass with this one
                XML_RPC2_Backend::setBackend('xmlrpcext');
                $this->backend = 'xmlrpcext';
                break;
            default:
                XML_RPC2_Backend::setBackend('php');
                break;
        }

        return parent::dispatch();
    }

    protected function isXmlRpcExt()
    {
        return ($this->backend == 'xmlrpcext');
    }

    function renderHtml()
    {
        ob_start();
        $this->getServer()->autoDocument();
        $result = ob_get_clean();

        if ($this->isXmlRpcExt()) {
            return utf8_decode($result);
        }

        return $result;
    }

    /*
    function renderXml() {

        return $this->getServer()->getResponse();
    }
    */

    function POST()
    {
        if ($this->isXmlRpcExt()) {
            return utf8_decode($this->getResponse());
        }

        return $this->getResponse();
    }

    function getResponse()
    {
        if ($this->isXmlRpcExt()) {
            return utf8_decode($this->getServer()->getResponse());
        }
        return $this->getServer()->getResponse();
    }
}
