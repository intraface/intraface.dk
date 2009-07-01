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

class Intraface_XMLRPC_Newsletter_Server0100 extends Intraface_XMLRPC_Server
{
    private $list;
    private $subscriber;

    private function factoryList($list_id)
    {
        $this->list = new NewsletterList($this->kernel, $list_id);

        if (!$this->list->doesListExist()) {
            require_once 'XML/RPC2/Exception.php';
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
        
        $list_id = $this->processRequestData($list_id);
        $email = $this->processRequestData($email);
        $name = $this->processRequestData($name);
        $ip = $this->processRequestData($ip);
        
        $subscriber = $this->factoryList($list_id);

        if (!$subscriber->subscribe(array('name' => $name, 'email' => $email, 'ip' => $ip), Intraface_Mail::factory())) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('an error occurred when trying to subscribe', -4);
        }

        return $this->prepareResponseData(true);

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

        $list_id = $this->processRequestData($list_id);
        $email = $this->processRequestData($email);
        
        $this->factoryList($list_id);

        if (!$this->subscriber->unsubscribe($email)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('you could not unsubscribe with ' .$email, -4);
        }

        return $this->prepareResponseData(true);
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

        $list_id = $this->processRequestData($list_id);
        $optin_code = $this->processRequestData($optin_code);
        $ip = $this->processRequestData($ip);
        
        $this->factoryList($list_id);

        if (!$this->subscriber->optIn($optin_code, $ip)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('your submission could not be confirmed', -4);
        }

        return $this->prepareResponseData(true);

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

        return $this->prepareResponseData(
            $list->getList()
        );
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

        $contact_id = $this->processRequestData($contact_id);
        
        $this->kernel->useModule('contact', true);

        $contact = new Contact($this->kernel, $contact_id);
        return $this->prepareResponseData(
            $contact->getNewsletterSubscriptions()
        );
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

        $contact_id = $this->processRequestData($contact_id);
        
        $this->kernel->useModule('contact', true);

        $contact = new Contact($this->kernel, $contact_id);
        return $this->prepareResponseData(
            $contact->needNewsletterOptin()
        );
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
        $this->checkCredentials($credentials);
        
        $code = $this->processRequestData($code);
        
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query('SELECT list_id FROM newsletter_subscriber WHERE code = ' . $db->quote($code, 'text'));
        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            return $this->prepareResponseData($row['list_id']);
        }
        return $this->prepareResponseData(false);
    }
}