<?php
/**
 * Authenticates a user
 *
 * @package  Intraface
 * @author   Lars Olesen <lars@legestue.net>
 * @since    0.1.0
 * @version  @package-version@
 */
class Intraface_Auth_User
{
    private $db;
    private $email;
    private $password;

    /**
     * Constructor
     *
     * @param object $db       Databaseobject
     * @param string $email    Username
     * @param string $password Password
     *
     * @return void
     */
    function __construct($db, $session_id, $email = NULL, $password = NULL)
    {
        $this->db         = $db;
        $this->email      = $email;
        $this->password   = $password;
        $this->session_id = $session_id;
    }

    /**
     * Auth
     *
     * @return object
     */
    public function auth()
    {
        $result = $this->db->query("SELECT id FROM user WHERE email = ".$this->db->quote($this->email, 'text')." AND password = ".$this->db->quote(md5($this->password), 'text'));

        if (PEAR::isError($result)) {
            throw Exception('result is an error' . $result->getMessage() . $result->getUserInfo());
        }

        if ($result->numRows() != 1) {
            return false;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

        $result = $this->db->exec("UPDATE user SET lastlogin = NOW(), session_id = ".$this->db->quote($this->session_id, 'text')." WHERE id = ". $this->db->quote($row['id'], 'integer'));
        if (PEAR::isError($result)) {
            throw new Exception('could not update user ' . $result->getMessage() . $result->getUserInfo());
        }
        
        $user = new Intraface_User($row['id']);
		if(!is_object($user) || $user->get('id') != $row['id']) {
		    throw new Exception('Unable to return a valid user object on login');
		}
        
        $_SESSION['intraface_logged_in_user_id'] = $user->getId();
        
        return $user;
    }
    
    function isLoggedIn()
    {
        $result = $this->db->query("SELECT id FROM user WHERE session_id = ".$this->db->quote($this->session_id, 'text'));
        if (PEAR::isError($result)) {
            throw new Exception('could not check if user is logged in ' . $result->getUserInfo());
        }

        if ($result->numRows() == 0) {
            return false;
        }

        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        return new Intraface_User($row['id']);
    }

    /**
     * logout()
     *
     * @return boolean
     */
    public function logout()
    {
        $result = $this->db->exec("UPDATE user SET session_id = " . $this->db->quote('', 'text') . " WHERE session_id = " . $this->db->quote($this->session_id, 'text'));

        if (PEAR::isError($result)) {
             throw new Exception('could not log user out ' . $result->getUserInfo());
        }
        return true;
    }
    
    /**
     * Returns an identification string on the user
     * 
     * @return string identification (email)
     */
    public function getIdentification() 
    {
        return $this->email;
    }
    
}