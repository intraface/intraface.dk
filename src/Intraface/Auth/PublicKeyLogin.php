<?php
/**
 * PublicKeyLogin
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Auth_PublicKeyLogin
{
    /**
     * @var object
     */
    private $db;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $session_id;

    /**
     * Constructor
     *
     * @param $session_id
     *
     * @return void
     */
    function __construct(MDB2_Driver_Common $db, $session_id, $key)
    {
        $this->db         = $db;
        $this->key        = $key;
        $this->session_id = $session_id;
    }

    /**
     * Authenticates
     *
     * @return object
     */
    public function auth()
    {
       $result = $this->db->query("SELECT id FROM intranet WHERE public_key = ".$this->db->quote($this->key, 'text'));
       if (PEAR::isError($result)) {
           throw new Exception($result->getUserInfo());
       }
       if ($result->numRows() == 0) {
           return false;
       }
       $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

       return new Intraface_Weblogin($this->session_id, new Intraface_Intranet($row['id']));
    }
}