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
class Intraface_Weblogin
{
    /**
     * @var object
     */
    private $db;

    /**
     * @var string
     */
    private $session_id;

    //public $intranet;
    //public $setting;

    /**
     * Constructor
     *
     * @param $session_id
     *
     * @return void
     */
    function __construct($session_id = '')
    {
        $this->db = MDB2::singleton(DB_DSN);

        if (PEAR::isError($this->db)) {
            throw new Exception($this->db->getMessage());
        }

        $this->session_id = $session_id;
    }

    /**
     * Auth
     *
     * @param string $type Can be private and public
     * @param string $key  The keyw to use
     *
     * @return integer (intranet id)
     */
    public function auth($type, $key)
    {
        if($type == 'private') {
            $result = $this->db->query("SELECT id FROM intranet WHERE private_key = " . $this->db->quote($key, 'text'));
            if(PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }
            if($result->numRows() == 0) {
                return false;
            }
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            return $row['id'];

        } elseif($type == 'public') {

            $result = $this->db->query("SELECT id FROM intranet WHERE public_key = ".$this->db->quote($key, 'text'));
            if(PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }
            if($result->numRows() == 0) {
                return false;
            }
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            return $row['id'];

        } else {
            trigger_error('Ugyldig type weblogin', E_USER_ERROR);
            return false;
        }

    }

    /**
     * Gets the session id
     *
     * @todo Should be renamed
     *
     * @deprecated
     *
     * @return string
     */
    function get()
    {
        return $this->session_id;
    }

    /**
     * Gets the session id
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    function getActiveIntranetId()
    {
        return 'active intranet id';
    }

    function hasModuleAccess($modulename)
    {
        // only needs to check whether the intranet has module access
        return true;
    }

    function hasIntranetAccess($intranet_id)
    {
        // only needs to check whether the intranet has module access
        return true;
    }
}