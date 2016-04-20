<?php
/**
 * Admin
 *
 * @package Admin
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_XMLRPC_Admin_Server extends Intraface_XMLRPC_Server
{
    /**
     * Gets the private_key for an intranet supplied with the master password
     *
     * @param string $master_password      Master password
     * @param string $intranet_identifier  Intranet identifier
     *
     * @return string with the private_key or false
     */
    public function getPrivateKey($master_password, $intranet_identifier)
    {
        if (!$this->checkMasterpassword($master_password)) {
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('master password not accepted', -2);
        }

        $intranet_identifier = $this->processRequestData($intranet_identifier);

        $db = MDB2::singleton(DB_DSN);

        if (PEAR::isError($db)) {
            throw new XML_RPC2_Exception('error accessing the database ' . $db->getUserInfo());
        }

        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $result = $db->query("SELECT id, name, private_key FROM intranet WHERE identifier = ".$db->quote($intranet_identifier, 'text')." AND identifier <> ''");

        if (PEAR::isError($result)) {
            throw new XML_RPC2_Exception('error querying the database ' . $result->getUserInfo());
        }

        if ($result->numRows() <> 1) {
            return false;
        }

        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

        if (empty($row['private_key'])) {
            return false;
        }

        return $this->prepareResponseData($row['private_key']);
    }

    /**
     * Checks whether an intranet has access to module
     *
     * @param struct $credentials credentials containing 'private_key' and 'session_id'
     * @param string $module the module to check for
     * @return boolean true or false
     */
    public function hasModuleAccess($credentials, $module)
    {
        if (!$this->checkCredentials($credentials)) {
            throw new XML_RPC2_Exception('error in credentials');
        }

        return $this->prepareResponseData(
            $this->kernel->intranet->hasModuleAccess($this->processRequestData($module))
        );
    }

       /**
     * Checking credentials
     *
     * @param struct $master_password
     *
     * @return boolean
     */
    private function checkMasterpassword($master_password)
    {
        if ($master_password != 'abcdefghijklmnopqrstuvwxyz123456789#') {
            return false;
        }
        return true;
    }
}
