<?php
/**
 * Contact-Server
 *
 * @package Contact
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
require_once 'Intraface/modules/contact/Contact.php';

class Intraface_XMLRPC_Contact_Server extends Intraface_XMLRPC_Server0100
{
    /**
     * @var string
     */
    protected $credentials;

    /**
     * Gets a contact
     *
     * @param  struct  $credentials Struct with credentials
     * @param  integer $id          Contact id
     *
     * @return array
     */
    public function getContact($credentials, $id)
    {
        $this->checkCredentials($credentials);

        $contact = new Contact($this->kernel, $id);
        if ($contact->getId() == 0) { // -4
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('contact does not exist', -4);
        }
        $contact_info = array_merge($contact->get(), $contact->address->get());
        $contact_info['id'] = $contact->get('id');
        $contact_info['type'] = $contact->get('type');
        if (!$contact_info) {
            return array();
        }

        return $this->prepareResponseData($contact_info);
    }

    /**
     * DEPRECATED: Authenticates a contact
     *
     * @deprecated
     * @see authenticateByKey()
     *
     * @param  struct  $credentials Credentials provided by intraface
     * @param  string  $contact_key The contact's key
     *
     * @return array
     */
    public function authenticateContact($credentials, $contact_key)
    {
        $this->checkCredentials($credentials);

        $contact = Contact::factory($this->kernel, 'code', $contact_key);
        if (!is_object($contact) or !$contact->get('id') > 0) {
            return false;
        }

        $contact_info = array_merge($contact->get(), $contact->address->get());
        $contact_info['id'] = $contact->get('id');

        if (!$contact_info) {
            return array();
        }

        return $this->prepareResponseData($contact_info);
    }

    /**
     * Get contacts by e-mail
     *
     * @param  struct  $credentials Credentials provided by intraface
     * @param  string  $email       E-mail for the contact
     *
     * @return array
     */
    public function getContactsByEmail($credentials, $email)
    {
        $this->checkCredentials($credentials);

        $contacts = array();
        $gateway = new Intraface_modules_contact_ContactGateway($this->kernel, new DB_Sql);
        foreach ($gateway->findByEmail($email) as $contact) {
            $contacts[] = $contact->get();
        }
        return $this->prepareResponseData($contacts);
    }

    /**
     * Authenticates a contact
     *
     * @param  struct  $credentials Credentials provided by intraface
     * @param  string  $contact_key The contact's key
     *
     * @return array
     */
    public function authenticateByKey($credentials, $contact_key)
    {
        $this->checkCredentials($credentials);

        $contact = Contact::factory($this->kernel, 'code', $contact_key);
        if (!is_object($contact) or !$contact->get('id') > 0) {
            return false;
        }

        $contact_info = array_merge($contact->get(), $contact->address->get());
        $contact_info['id'] = $contact->get('id');

        if (!$contact_info) {
            return array();
        }

        return $this->prepareResponseData($contact_info);
    }

    /**
     * Authenticates a contact
     *
     * @param  struct  $credentials Credentials provided by intraface
     * @param  string  $username    E-mail
     * @param  string  $password    Password
     *
     * @return array
     */
    public function authenticateByUsernameAndPassword($credentials, $username, $password)
    {
        $this->checkCredentials($credentials);

        $contact = Contact::factory($this->kernel, 'username', array('username' => $username, 'password' => $password));
        if (!is_object($contact) or !$contact->get('id') > 0) {
            return false;
        }

        $contact_info = array_merge($contact->get(), $contact->address->get());
        $contact_info['id'] = $contact->get('id');

        if (!$contact_info) {
            return array();
        }

        return $this->prepareResponseData($contact_info);
    }

    /**
     * Saves a contact
     *
     * @param  struct $credentials Credentials provided by intraface
     * @param  array  $input       Remember to include id key
     *
     * @return boolean
     */
    public function saveContact($credentials, $input)
    {
        $this->checkCredentials($credentials);

        if (!is_array($input)) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('input is not an array', -5);
        }

        if (!isset($input['id'])) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('input must contain an id key', -5);
        }

        $input = $this->processRequestData($input);

        $contact = new Contact($this->kernel, $input['id']);

        if (!$contact->save($input)) {
            $contact->error->view(); // -6
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('could not update contact', -6);
        }

        return true;
    }

    /**
     * Gets available keywords for the contacts
     *
     * @param struct  $credentials Credentials provided by intraface
     *
     * @return array Keywords
     */
    public function getKeywords($credentials)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        $this->kernel->useShared('keyword');

        $contact = new Contact($this->kernel);
        $contact->getKeywords();

        return $this->prepareResponseData($contact->keywords->getAllKeywords());
    }

    /**
     * Gets connected keywords to a contact
     *
     * @param struct  $credentials Credentials provided by intraface
     * @param integer $contact_id  Contact id
     *
     * @return array Keywords
     */
    public function getConnectedKeywords($credentials, $contact_id)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        $this->kernel->useShared('keyword');
        $contact = new Contact($this->kernel, $contact_id);
        if (!$contact->get('id') > 0) {
            return 0;
        }
        $keywords = array();
        $keywords = $contact->getKeywordAppender()->getConnectedKeywords();
        return $this->prepareResponseData($keywords);
    }


    /**
     * Gets intranet permissions to use with a menu
     *
     * Might be deprecated in the future so use with caution
     *
     * @param  struct $credentials Credentials provided by intraface
     *
     * @return array
     */
    public function getIntranetPermissions($credentials)
    {
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

        return $this->prepareResponseData($permissions);
    }

    /**
     * Checking credentials
     *
     * @param struct $credentials
     * @return array
     */
    protected function checkCredentials($credentials)
    {
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
            throw new XML_RPC2_FaultException('Access to the intranet denied. The private key is probably wrong.', -5);
        }

        $this->kernel = new Intraface_Kernel();
        $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

        return true;
    }
}
