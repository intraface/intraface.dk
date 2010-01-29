<?php
/**
 * Main XMLRPC server class to extend all other Serves from
 *
 * Gives ability to encode and decode data correct.
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC
 * @author   Sune Jensen <sj@sunet.dk>
 * @version  @package-version@
 */

/**
 * Main XMLRPC server class to extend all other Serves from
 *
 * Gives ability to encode and decode data correct.
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC
 * @author   Sune Jensen <sj@sunet.dk>
 * @version  @package-version@
 */
class Intraface_XMLRPC_Server
{
    /**
     * @var struct $credentials
     */
    protected $credentials;

    /**
     * @var object $kernel intraface kernel
     */
    protected $kernel;

    protected $valid_encodings = array('utf-8', 'iso-8859-1');

    /**
     * Constructor
     *
     * @param string  $encoding The encoding wich the server recieves and returns data in
     *
     * @return void
     */
    public function __construct($encoding = 'utf-8')
    {
        if (!in_array($encoding, $this->valid_encodings)) {
            throw new Exception('Invalid encoding: '.$encoding.'. Should either be utf-8 or iso-8859');
        }
        $this->encoding = $encoding;
    }

    /**
     * Checks credentials
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return array
     */
    protected function checkCredentials($credentials)
    {
        $this->credentials = $credentials;

        if (count($credentials) != 2) { // -4
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('wrong argument count in $credentials - got ' . count($credentials) . ' arguments - need 2', -4);
        }
        if (empty($credentials['private_key'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('supply a private_key', -5);
        }
        if (empty($credentials['session_id'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('supply a session_id', -5);
        }

        $auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), $credentials['session_id'], $credentials['private_key']);
        $weblogin = $auth_adapter->auth();

        if (!$weblogin) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('access to intranet denied', -2);
        }

        $this->kernel = new Intraface_Kernel($credentials['session_id']);
        $this->kernel->weblogin = $weblogin;
        $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

        // makes intranet_id accessable in Doctrine
        Intraface_Doctrine_Intranet::singleton($this->kernel->intranet->getId());

        return true;
    }

    /**
     * Prepares response to be sent with the correct UTF-8 encoding.
     *
     * @param mixed $values Array or string to decode
     * @return mixed UTF8 decoded request
     */
    protected function prepareResponseData($values)
    {
        if($this->encoding == 'utf-8') {
            return $this->recursiveMap('utf8_encode', $values);
        }
        return $values;
    }

    /**
     * Process data from client, so that data is returned with the correct encoding.
     *
     * @param mixed $values Array or string to decode
     * @return mixed correct encoded response
     */
    protected function processRequestData($values)
    {
        if($this->encoding == 'utf-8') {
            return $this->recursiveMap('utf8_decode', $values);
        }
        return $values;
    }

    protected function recursiveMap($function, $values)
    {
        if (is_string($values)) {
            return call_user_func($function, $values);
        } elseif (is_array($values)) {
            foreach ($values AS $key => $value) {
                $values[$key] = $this->recursiveMap($function, $value);
            }
            return $values;
        } else {
            return $values;
        }
    }

}
