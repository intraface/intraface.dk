<?php
/**
 * Contact-Server
 *
 * @package Contact
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'Intraface/Weblogin.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/modules/contact/Contact.php';
require_once 'XML/RPC2/Server.php';
require_once 'MDB2.php';

class Intraface_XMLRPC_Contact {

    private $credentials;
    //private $kernel;

    //function __construct() {
    function __construct($kernel = '') {
        //$this->kernel = $kernel;
    }

    /**
     * Gets a contact
     *
     * @param  struct  $credentials
     * @param  integer $id
     * @return array
     */
    function getContact($credentials, $id) {

        $this->checkCredentials($credentials);

        $contact = new Contact($this->kernel, $id);
        if (!$contact->get('id') > 0) { // -4
            throw new XML_RPC2_FaultException('contact does not exist', -4);
        }
        $contact_info = array_merge($contact->get(), $contact->address->get());
        $contact_info['id'] = $contact->get('id');
        if (!$contact_info) {
            return array();
        }

        return $contact_info;
    }

    /**
     * Authenticates a contact
     *
     * @param  struct  $credentials
     * @param  string  $contact_key
     * @return array
     */
    function authenticateContact($credentials, $contact_key) {
        $this->checkCredentials($credentials);

        $contact = Contact::factory($this->kernel, 'code', $contact_key);
        if (!is_object($contact) OR !$contact->get('id') > 0) {
            return false;
        }

        $contact_info = array_merge($contact->get(), $contact->address->get());
        $contact_info['id'] = $contact->get('id');

        if (!$contact_info) {
            return array();
        }

        return $contact_info;
    }

    /**
     * Saves a contact
     *
     * @param  struct $credentials
     * @param  array  $input (remember to include id key)
     * @return boolean
     */
    function saveContact($credentials, $input) {
        $this->checkCredentials($credentials);

        if (!is_array($input)) { // -5
            throw new XML_RPC2_FaultException('input is no an array', -5);
        }

        $values = $input;

        $contact = new Contact($this->kernel, $values['id']);

        if (!$contact->save($values)) {
            $contact->error->view(); // -6
            throw new XML_RPC2_FaultException('could not update contact', -6);
        }

        return true;
    }
    /*
    function sendLoginEmail($arg) {
        if (count($arg) != 2) {
            return new IXR_Error(-4, 'Der er ikke det rigtige antal argumenter til sendLoginEmail()');
        }

        if (is_object($return = $this->checkCredentials($arg[0]))) {
            return $return;
        }

        if (!is_numeric($arg[1])) {
            return new IXR_Error(-5, 'Det andet parameter er ikke et numerisk');
        }

        $id = $arg[1];

        $contact = new Contact($this->kernel, $id);

        if (!is_object($contact) OR !$contact->get('id') > 0) {
            return new IXR_Error(-5, 'Kontakten fandtes ikke ' .$arg[1]);
        }

        if (!$contact->sendLoginEmail()) {
            return new IXR_Error(-6, 'Du kunne ikke sende email ' .$arg[1]);
        }

        return 1;
    }
    */

    /**
     * Gets available keywords for the contacts
     *
     * @param struct  $credentials Credentials provided by intraface
     * @param integer $contact_id  Contact id
     *
     * @return array Keywords
     */
    function getKeywords($credentials) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        $contact = new Contact($this->kernel);
        $contact->getKeywords();

        return $contact->keywords->getAllKeywords();
    }

    /**
     * Gets connected keywords to a contact
     *
     * @param struct  $credentials Credentials provided by intraface
     * @param integer $contact_id  Contact id
     *
     * @return array Keywords
     */
     function getConnectedKeywords($credentials, $contact_id) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $contact = new Contact($this->kernel, $contact_id);
        if (!$contact->get('id') > 0) {
            return 0;
        }
        $contact->getKeywords();
        $keywords = array();
        $keywords = $contact->keywords->getConnectedKeywords();
        return $keywords;
    }


    /**
     * Gets intranet permissions to use with a menu
     *
     * Might be deprecated in the future so use with caution
     *
     * @param  struct $credentials
     *
     * @return array
     */
    public function getIntranetPermissions($credentials) {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $permissions = array();

        if ($this->kernel->intranet->hasModuleAccess('newsletter')) {
            $permissions[] = 'newsletter';
        }

        if ($this->kernel->intranet->hasModuleAccess('todo')) {
            $permissions[] = 'todo';
        }

        if ($this->kernel->intranet->hasModuleAccess('debtor')) {
            $permissions[] = 'debtor';
        }

        return $permissions;
    }

    /**
     * Checking credentials
     *
     * @param struct $credentials
     * @return array
     */
    function checkCredentials($credentials) {
        /*
        if (is_object($this->kernel) AND is_object($this->kernel->intranet)) {
            return true;
        }
        */

        if (count($credentials) != 2) { // -4
            throw new XML_RPC2_FaultException('wrong argument count in $credentials - got ' . count($credentials) . ' arguments - need 2', -4);
        }
        if (empty($credentials['private_key'])) { // -5
            throw new XML_RPC2_FaultException('supply a private_key', -5);
        }
        if (empty($credentials['session_id'])) { // -5
            throw new XML_RPC2_FaultException('supply a session_id', -5);
        }

        $weblogin = new Weblogin('some session');
        if (!$intranet_id = $weblogin->auth('private', $credentials['private_key'], $credentials['session_id'])) {
            throw new XML_RPC2_FaultException('contact says access to intranet - please supply a valid private key', -2);
        }

        $this->kernel = new Kernel();
        $this->kernel->intranet = new Intranet($intranet_id);
        $this->kernel->setting = new Setting($this->kernel->intranet->get('id'));

        if (!is_object($this->kernel->intranet)) { // -2
            throw new XML_RPC2_FaultException('could not create intranet', -2);
        }

        return true;
    }

}
?>