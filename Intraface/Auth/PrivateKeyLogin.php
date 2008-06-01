<?php
/**
 * Weblogin
 *
 * @todo this should be a special case of user
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Auth_PrivateKeyLogin
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
     * @param $db         MDB2 database object
     * @param $key        Private key supplied by intraface
     * @param $session_id Session id
     *
     * @return void
     */
    function __construct($db, $session_id, $key)
    {
        $this->db         = $db;
        $this->session_id = $session_id;
        $this->key        = $key;
    }

    /**
     * Auth
     *
     * @return object
     */
    public function auth()
    {
        $result = $this->db->query("SELECT id FROM intranet WHERE private_key = " . $this->db->quote($this->key, 'text'));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        if($result->numRows() == 0) {
            return false;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

        return new Intraface_Weblogin($this->session_id, new Intraface_Intranet($row['id']));
    }
}