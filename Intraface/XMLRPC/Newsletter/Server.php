<?php
/**
 * Newsletter XMLRPC Server
 *
 * @package Newsletter
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'XML/RPC2/Server.php';
require_once 'Intraface/Weblogin.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/modules/contact/Contact.php';

class Intraface_XMLRPC_Newsletter_Server
{

    private $kernel;
    private $list;
    private $subscriber;
    private $credentials;

    function factoryList($list_id)
    {
        $this->kernel->useModule('newsletter');

        $this->list = new NewsletterList($this->kernel, $list_id);

        if (!$this->list->doesListExist()) {
            throw new XML_RPC2_FaultException('the newsletter list does not exist', -2);
        }

        $this->subscriber = new NewsletterSubscriber($this->list);
    }

    /**
     * Subscribe to newsletter list
     *
     * @param struct  $credentials Must include private_key and session_id
     * @param integer $list_id     List to subscribe to
     * @param string  $email       Email to subscribe
     * @param string  $ip          Which email subscribes
     *
     * @return boolean
     */
    function subscribe($credentials, $list_id, $email, $ip)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->factoryList($list_id);

        if (!$this->subscriber->subscribe(array('email' => $email, 'ip' => $ip))) {
            throw new XML_RPC2_FaultException('an error occurred when trying to subscribe: ' . implode(',', $this->subscriber->error->message), -4);
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
    function unsubscribe($credentials, $list_id, $email)
    {
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->factoryList($list_id);

        if (!$this->subscriber->unsubscribe($email)) {
            throw new XML_RPC2_FaultException('Du kunne ikke framelde dig ' .$arg[1], -4);
        }

        return 1;
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

        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->factoryList($list_id);

        if (!$this->subscriber->optIn($optin_code, $ip)) {
            throw new XML_RPC2_FaultException('Du kunne ikke bekræfte din tilmelding', -4);
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
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

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
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

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
        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->kernel->useModule('contact', true);

        $contact = new Contact($this->kernel, $contact_id);
        return $contact->needNewsletterOptin();
    }

    /**
     * Checking credentials
     *
     * @param struct $credentials Must include private_key and session_id
     *
     * @return array
     */
    function checkCredentials($credentials)
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

        $weblogin = new Weblogin();
        if (!$intranet_id = $weblogin->auth('private', $credentials['private_key'], $credentials['session_id'])) {
            throw new XML_RPC2_FaultException('access to intranet denied', -2);
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