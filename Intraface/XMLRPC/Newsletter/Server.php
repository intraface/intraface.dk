<?php
/**
 * Newsletter XMLRPC Server
 *
 * @package Newsletter
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/modules/newsletter/NewsletterList.php';
require_once 'Intraface/modules/newsletter/NewsletterSubscriber.php';

class Intraface_XMLRPC_Newsletter_Server
{
    private $kernel;
    private $list;
    private $subscriber;
    private $credentials;

    private function factoryList($list_id)
    {
        $this->list = new NewsletterList($this->kernel, $list_id);

        if (!$this->list->doesListExist()) {
            throw new XML_RPC2_FaultException('the newsletter list does not exist', -2);
        }

        return ($this->subscriber = new NewsletterSubscriber($this->list));
    }

    /**
     * Subscribe to newsletter list
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param integer $list_id     List to subscribe to
     * @param string  $email       Email to subscribe
     * @param string  $name        Name to subscribe
     * @param string  $ip          Which email subscribes
     *
     * @return boolean
     */
    public function subscribe($credentials, $list_id, $email, $name = '', $ip = '')
    {
        $this->checkCredentials($credentials);

        $subscriber = $this->factoryList($list_id);

        if (!$subscriber->subscribe(array('name' => $name, 'email' => $email, 'ip' => $ip))) {
            echo $subscriber->error->view();
            throw new XML_RPC2_FaultException('an error occurred when trying to subscribe', -4);
        }

        return true;

    }

    /**
     * Unsubscribe from newsletter list
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param integer $list_id     List would you unsubscribe from
     * @param string  $email       Email to unsubscribe
     *
     * @return boolean
     */
    public function unsubscribe($credentials, $list_id, $email)
    {
        $this->checkCredentials($credentials);

        $this->factoryList($list_id);

        if (!$this->subscriber->unsubscribe($email)) {
            throw new XML_RPC2_FaultException('you could not unsubscribe with ' .$email, -4);
        }

        return true;
    }

    /**
     * Opt in for a newsletter
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param integer $list_id     Which list should be opted in to
     * @param string  $optin_code  The code to optin with
     * @param string  $ip          Which ip is optin from
     *
     * @return boolean
     */
    function optin($credentials, $list_id, $optin_code, $ip)
    {
        $this->checkCredentials($credentials);

        $this->factoryList($list_id);

        if (!$this->subscriber->optIn($optin_code, $ip)) {
            throw new XML_RPC2_FaultException('your submission could not be confirmed', -4);
        }

        return true;

    }

    /**
     * Gets all newsletter lists on an intranet
     *
     * @param struct $credentials Must include private_key and session_id
     *
     * @return array
     */
    function getNewsletterList($credentials)
    {
        $this->checkCredentials($credentials);

        if (!$this->kernel->intranet->hasModuleAccess('newsletter')) {
            return array();
        }
        $this->kernel->module('newsletter');

        $list = new NewsletterList($this->kernel);

        return $list->getList();
    }

    /**
     * Gets all the users subscriptions
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param integer $contact_id  The contact id
     *
     * @return array
     */
    function getSubscriptions($credentials, $contact_id)
    {
        $this->checkCredentials($credentials);

        $this->kernel->useModule('contact', true);

        $contact = new Contact($this->kernel, $contact_id);
        return $contact->getNewsletterSubscriptions();
    }

    /**
     * Gets all the lists that needs the contacts attention for optin
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param integer $contact_id  The contact id
     *
     * @return array
     */
    function needOptin($credentials, $contact_id)
    {
        $this->checkCredentials($credentials);

        $this->kernel->useModule('contact', true);

        $contact = new Contact($this->kernel, $contact_id);
        return $contact->needNewsletterOptin();
    }

    /**
     * Gets the list id from the optin code
     *
     * This method is highly experimental, and will probably be deprecated in
     * the future.
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param string  $code        The optin code
     *
     * @return integer with list id
     */
    function getListIdFromOptinCode($credentials, $code)
    {
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query('SELECT list_id FROM newsletter_subscriber WHERE code = ' . $db->quote($code, 'text'));
        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            return $row['list_id'];
        }
        return false;
    }

    /**
     * Checking credentials
     *
     * @param struct $credentials Must include private_key and session_id
     *
     * @return boolean or throws an error
     */
    private function checkCredentials($credentials)
    {
        if (count($credentials) != 2) { // -4
            throw new XML_RPC2_FaultException('wrong argument count in $credentials - got ' . count($credentials) . ' arguments - need 2', -4);
        }
        if (empty($credentials['private_key'])) { // -5
            throw new XML_RPC2_FaultException('supply a private_key', -5);
        }
        if (empty($credentials['session_id'])) { // -5
            throw new XML_RPC2_FaultException('supply a session_id', -5);
        }

        $auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), $credentials['session_id'], $credentials['private_key']);
        $weblogin = $auth_adapter->auth();
        
        if (!$weblogin) {
            throw new XML_RPC2_FaultException('access to intranet denied', -2);
        }
        
        $this->kernel = new Intraface_Kernel();
        $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
        $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

        return true;
    }
}