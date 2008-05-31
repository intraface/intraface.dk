<?php
/**
 * Authenticates a user
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

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function authenticate($adapter)
    {
        if ($object = $adapter->auth()) {
        	$this->notifyObservers('login', ' logged in');    
        } else {
            $this->notifyObservers('login', ' could not login');
        }
        
        return ($_SESSION['user'] = $object);
    }

    /**
     * hasIdentity()
     *
     * @return mixed user id or false
     */
    public function hasIdentity()
    {
		if (!empty($_SESSION['user'])) {
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
        unset($_SESSION['user']);
    }

	public function getIdentity()
	{
	    if ($this->hasIdentity()) {
	        return $_SESSION['user'];
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