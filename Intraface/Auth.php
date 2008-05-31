<?php
/**
 * Authenticates a user
 *
 * @package  Intraface
 * @author   Lars Olesen <lars@legestue.net>
 * @since    0.1.0
 * @version  @package-version@
 */

define('LOGIN_ERROR_WRONG_CREDENTIALS', 0);
define('LOGIN_ERROR_ALREADY_LOGGED_IN', -1);

class Intraface_Auth
{
    private $db;
    private $session_id;
    private $observers = array();

    /**
     * Constructor
     *
     * @param object $db         Databaseobject
     * @param string $session_id Session id
     *
     * @return void
     */
    function __construct($session_id)
    {
        $this->db = MDB2::singleton(DB_DSN);
        $this->session_id = $session_id;
    }

    /**
     * login()
     *
     * @param string $email    Email
     * @param string $password Password
     *
     * @return mixed | boolean of user object
     */
    public function login($email, $password)
    {
        $result = $this->db->query("SELECT id FROM user WHERE email = ".$this->db->quote($email, 'text')." AND password = ".$this->db->quote(md5($password), 'text'));

        if (PEAR::isError($result)) {
            trigger_error('result is an error' . $result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
            return false;
        }

        if ($result->numRows() != 1) {
            return false;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $result = $this->db->exec("UPDATE user SET lastlogin = NOW(), session_id = ".$this->db->quote($this->session_id, 'text')." WHERE id = ". $this->db->quote($row['id'], 'integer'));
        if (PEAR::isError($result)) {
            trigger_error('could not update user ' . $result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        $this->notifyObservers('login', $email .' logged in');

        return true;
    }

    /**
     * weblogin()
     *
     * @todo how do we make this more clever, so you can ask afterwards whether
     *       a user is logged in
     *
     * @param string $type Which type
     * @param string $key  The key
     *
     * @return	mixed / boolean or weblogin object
     */
    public function weblogin($type, $key)
    {
        switch ($type) {
            case 'public':
                $result = $this->db->query("SELECT id FROM intranet WHERE public_key = ".$this->db->quote($key, 'text'));
                break;
            case 'private':
                $result = $this->db->query("SELECT id FROM intranet WHERE private_key = ".$this->db->quote($key, 'text'));
                break;
            default:
                trigger_error('unknown weblogin type', E_USER_ERROR);
                return false; // this has to be return to make sure script will never continue
        }

        if (PEAR::isError($result)) {
            trigger_error('result is an error', E_USER_ERROR);
            return false;
        }

        if ($result->numRows() == 0) {
            return false;
        }

        return true;

    }

    /**
     * isLoggedIn()
     *
     * @return mixed user id or false
     */
    public function isLoggedIn()
    {
        $result = $this->db->query("SELECT id FROM user WHERE session_id = ".$this->db->quote($this->session_id, 'text'));
        if (PEAR::isError($result)) {
            trigger_error('could not check if user is logged in ' . $result->getUserInfo(), E_USER_ERROR);
            return false;
        }

        if ($result->numRows() == 0) {
            return false;
        }

        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        return $row['id'];
    }

    /**
     * logout()
     *
     * TODO This should probably be in a user instead
     *
     * @param void
     *
     * @return boolean
     */
    public function logout()
    {
        $result = $this->db->exec("UPDATE user SET session_id = " . $this->db->quote('', 'text') . " WHERE session_id = " . $this->db->quote($this->session_id, 'text'));

         if (PEAR::isError($result)) {
             trigger_error('could not log user out ' . $result->getUserInfo(), E_USER_ERROR);
             return false;
         }

        return true;
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