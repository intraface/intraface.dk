<?php
/**
 * @author Lars Olesen <lars@legestue.net>
 */

require '../../common.php';
require '../XmlRpcServer.php';

class NewsletterServer extends XmlRpcServer {

    var $kernel;
    var $list;
    var $subscriber;
    var $credentials;

    function NewsletterServer() {
        XmlRpcServer::XmlRpcServer();

        $this->addCallback(
            'subscriber.subscribe',
            'this:subscribe',
            array('boolean', 'struct', 'integer', 'string', 'string'),
            'Returns true / false'
        );

        $this->addCallBack(
            'subscriber.unsubscribe',
            'this:unsubscribe',
            array('boolean', 'struct', 'integer', 'string'),
            'Returns true / false'
        );

        $this->addCallBack(
            'subscriber.optin',
            'this:optin',
            array('boolean', 'struct', 'integer', 'string', 'string'),
            'Returns true / false'
        );

        $this->addCallBack(
            'subscriber.getSubscriptions',
            'this:getSubscriptions',
            array('array', 'struct', 'integer'),
            'Returns array with all subscriptions'
        );

        $this->addCallBack(
            'subscriber.needOptin',
            'this:needOptin',
            array('array', 'struct', 'integer'),
            'Returns array with all newslists with no optin'
        );

        $this->addCallBack(
            'list.getList',
            'this:getList',
            array('array', 'struct'),
            'Returns array with all available lists (only optional lists)'
        );

        $this->serve();
    }

    function factoryList($list_id) {
        $this->kernel->useModule('newsletter');

        $this->list = new NewsletterList($this->kernel, $list_id);

        if (!$this->list->doesListExist()) {
            return new IXR_Error(-2, 'Listen eksisterer ikke');
        }

        $this->subscriber = new NewsletterSubscriber($this->list);

    }

    /**
     * Metode til at tilmelde sig nyhedsbrevet
     *
     * @param struct $arg
     * [0] $credentials
     * [1] $email
     * [2] $ip
     */

    function subscribe($arg) {
        $credentials = $arg[0];
        $list_id = intval($arg[1]);
        $email = $arg[2];
        $ip = $arg[3];

        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->factoryList($list_id);

        if (!$this->subscriber->subscribe(array('email'=>$email, 'ip'=>$ip))) {
            return new IXR_Error(-4, 'Du kunne ikke tilmelde dig ' . $email);
        }

        return 1;
    }

    /**
     * Metode til at tilmelde sig nyhedsbrevet
     *
     * @param struct $arg
     * [0] $credentials
     * [1] $email
     */

    function unsubscribe($arg) {
        if (is_object($return = $this->checkCredentials($arg[0]))) {
            return $return;
        }

        $this->factoryList($arg[1]);

        if (!$this->subscriber->unsubscribe($arg[2])) {
            return new IXR_Error(-4, 'Du kunne ikke framelde dig ' .$arg[1]);
        }

        return 1;
    }

    /**
     * Metode til at tilmelde sig nyhedsbrevet
     *
     * @param struct $arg
     * [0] $credentials
     * [1] $optincode
     * [2] $ip
     */
    function optin($arg) {
        $credentials = $arg[0];

        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }

        $this->factoryList($arg[1]);

        $optin_code = $arg[2];
        $ip = $arg[3];

        if (!$this->subscriber->optIn($optin_code, $ip)) {
            return new IXR_Error(-4, 'Du kunne ikke bekræfte din tilmelding');
        }

        return 1;

    }

    function getList($arg) {

        $credentials = $arg;

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

    function getSubscriptions($arg) {
        $credentials = $arg[0];

        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        $this->kernel->useModule('contact');

        $contact = new Contact($this->kernel, $arg[1]);
        return $contact->getNewsletterSubscriptions();

    }

    function needOptin($arg) {
        $credentials = $arg[0];

        if (is_object($return = $this->checkCredentials($credentials))) {
            return $return;
        }
        $this->kernel->useModule('contact');

        $contact = new Contact($this->kernel, $arg[1]);
        return $contact->needNewsletterOptin();

    }

}

$server = new NewsletterServer();
?>