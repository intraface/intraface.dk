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
class Intraface_Weblogin implements Intraface_Identity
{
    /**
     * @var string
     */
    private $session_id;

    /**
     * @var object
     */
    private $intranet;

    /**
     * Constructor
     *
     * @param $session_id Session id
     * @param $intranet   Intranet
     *
     * @return void
     */
    function __construct($session_id, $intranet)
    {
        $this->session_id = $session_id;
        $this->intranet = $intranet;
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
        return $this->intranet->getId();
    }

    function hasModuleAccess($modulename)
    {
        return $this->intranet->hasModuleAccess($modulename);
    }

    function hasIntranetAccess($intranet_id)
    {
        if ($this->intranet->getId() == $intranet_id) {
            return true;
        }
        return false;
    }

    function getId()
    {
        return 0;
    }
}
