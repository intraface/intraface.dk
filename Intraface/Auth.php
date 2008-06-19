<?php
/**
 * Keeps track of logged in users
 *
 * @package  Intraface
 * @author   Lars Olesen <lars@legestue.net>
 * @since    0.1.0
 * @version  @package-version@
 */
class Intraface_Auth
{
    private $identity;
    private $observers = array();
    private $session_id;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($session_id)
    {
        $this->session_id = $session_id;
    }

    public function authenticate($adapter)
    {
        if ($object = $adapter->auth()) {
            $this->notifyObservers('login', ' logged in');
        } else {
            $this->notifyObservers('login', ' could not login');
        }

        return ($object);
    }

    /**
     * hasIdentity()
     *
     * @return mixed user id or false
     */
    public function hasIdentity()
    {
        if (!empty($_SESSION['intraface_logged_in_user_id'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * logout()
     *
     * @return boolean
     */
    public function clearIdentity()
    {
        $_SESSION['user'] = '';
        unset($_SESSION['user']);
        session_destroy();
        return true;
    }

    public function getIdentity($db)
    {
        if ($this->hasIdentity()) {
            $adapter = new Intraface_Auth_User($db, $this->session_id);
            if(!$user = $adapter->isLoggedIn()) {
                throw new Exception('No valid user was found');
            }
            // $user->clearCachedPermission();
            return $user;
        }
        return false;
    }

    /**
     * Redirects to login
     *
     * @param string $msg Explanation
     *
     * @return void
     */
    static public function toLogin($msg = '')
    {
        if (empty($msg)) {
            header('Location: '.PATH_WWW.'main/login.php');
            exit;
        } else {
            header('Location: '.PATH_WWW.'main/login.php?msg='.urlencode($msg));
            exit;
        }
    }

    /**
     * Implements the observer pattern
     *
     * @param object $observer
     *
     * @return boolean
     */
    public function attachObserver($observer)
    {
        $this->observers[] = $observer;
        return true;
    }

    /**
     * Notifies observers
     *
     * @param string $code Which code
     * @param string $msg  Which message to pass to observers
     */
    private function notifyObservers($code, $msg)
    {
        foreach ($this->getObservers() AS $observer) {
            $observer->update($code, $msg);
        }
        return true;
    }

    /**
     * Implements the observer pattern
     *
     * @return array with observers
     */
    public function getObservers()
    {
        return $this->observers;
    }
}