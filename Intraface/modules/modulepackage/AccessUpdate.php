<?php
/**
 * This class grants module access according to module packages an intranet has.
 * It is used by the automated script and is run on if there is any instant changes in modulepackages.
 *
 * @package Intraface_modules_modulepackage
 * @author Sune Jensen
 * @version 0.0.1
 */
class Intraface_modules_modulepackage_AccessUpdate
{

    /**
     * @var object
     */
    private $kernel;

    /**
     * @var error;
     */

    /**
     * Constructor
     *
     * @param object kernel Kernel
     *
     * @return void
     */
    function __construct()
    {
        $this->error = new Intraface_Error;
    }

    /**
     * Run the AccessUpdate and applies module access acording to module packages
     *
     * @param integer intranet_id id on intranet, and the access update will only run on this intranet.
     * @return boolean true on success, false on failure
     */
    public function run($intranet_id = 0)
    {
        $db = MDB2::singleton(DB_DSN);
        if (PEAR::isError($db)) {
            trigger_error('Error in connecting to db: '.$db->getUserInfo(), E_USER_ERROR);
            exit;
        }
        $package_removed = 0;
        $package_added = 0;

        if ($intranet_id != 0) {
            $sql_extra = 'AND intranet_id = '.$db->quote($intranet_id, 'integer');
        } else {
            $sql_extra = '';
        }

        // We login to the intranet maintenance intranet
        $auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), session_id(), INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY);
        $weblogin = $auth_adapter->auth();
        if (!$intranet_id = $weblogin->getActiveIntranetId()) {
            throw new Exception("Unable to log in to intranet maintenance intranet");
        }

        $kernel = new Intraface_Kernel();
        $kernel->weblogin = $weblogin;
        $kernel->intranet = new Intraface_Intranet($intranet_id);
        $kernel->setting = new Intraface_Setting($kernel->intranet->get('id'));
        $kernel->useModule('intranetmaintenance');

        // first we remove access to ended packages.
        $result = $db->query("SELECT id, intranet_id, module_package_id FROM intranet_module_package WHERE active = 1 AND ((status_key = 2 AND end_date < NOW()) OR status_key = 3) ".$sql_extra);
        if (PEAR::isError($result)) {
            die('HER');
            trigger_error("Error in query for removing acces in ModulePackageManagerAccessUpdate->run :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        while($row = $result->fetchRow()) {
            $modulepackage = new Intraface_modules_modulepackage_ModulePackage($row['module_package_id']);
            $intranet = new IntranetMaintenance($row['intranet_id']);

            $modules = $modulepackage->get('modules');
            if (is_array($modules) && count($modules) > 0) {
                foreach($modules AS $module) {
                    if (!$intranet->removeModuleAccess($module['module'])) {
                        trigger_error('Error in removing access to module '.$module['module'].' for intranet '.$row['intranet_id'], E_USER_NOTICE);
                    }
                }
            }
            $update = $db->exec('UPDATE intranet_module_package SET status_key = 4 WHERE id = '.$db->quote($row['id'], 'integer'));
            if (PEAR::isError($update)) {
                trigger_error('Error in exec: '.$update->getUserInfo(), E_USER_ERROR);
                exit;
            }
            $package_removed += $update;
        }

        // then we set access to new packages.
        $result = $db->query("SELECT id, intranet_id, module_package_id FROM intranet_module_package WHERE active = 1 AND start_date <= NOW() AND status_key = 1 ".$sql_extra);
        if (PEAR::isError($result)) {
            trigger_error("Error in query for removing acces in ModulePackageManagerAccessUpdate->run :".$result->getUserInfo(), E_USER_ERROR);
            exit;
        }

        while($row = $result->fetchRow()) {
            $modulepackage = new Intraface_modules_modulepackage_ModulePackage($row['module_package_id']);

            // we prepare to give the intranet access
            $intranet = new IntranetMaintenance($row['intranet_id']);
            // we prepage to give the users access
            $user = new UserMaintenance();
            $users = $user->getList($kernel);
            foreach($users AS $key => $user) {
                $users[$key] = new UserMaintenance($user['id']);
            }

            $modules = $modulepackage->get('modules');
            if (is_array($modules) && count($modules) > 0) {
                // First we give access to the intranet
                foreach($modules AS $module) {
                    $module_object = ModuleMaintenance::factory($module['module']);
                    if (!$intranet->setModuleAccess($module['module'])) {
                        trigger_error("Error in giving access to module ".$module['module'].' for intranet '.$row['intranet_id'], E_USER_NOTICE);
                        $this->error->set('we could not give your intrnaet access to your modules');
                    } else {
                        // then we give access to alle the users in the intranet
                        foreach($users AS $user) {
                            if (!$user->setModuleAccess($module['module'], $row['intranet_id'])) {
                                trigger_error('Error in giving access to module '.$module['module'].' for user '.$user->get('username').' in intranet '.$row['intranet_id'], E_USER_NOTICE);
                                $this->error->set('we could not give all users access to your modules');
                            } else {
                                // And lastly we give all subaccess
                                $sub_access_array = $module_object->get('sub_access');
                                foreach($sub_access_array AS $sub_access) {
                                    if (!$user->setSubAccess($module['module'], $sub_access['id'], $row['intranet_id'])) {
                                        trigger_error('Error in giving subaccess to '.$sub_access['name'].' in module '.$module['module'].' for user '.$user->get('username').' in intranet '.$row['intranet_id'], E_USER_NOTICE);
                                        $this->error->set('we could not give all users access to your modules');
                                    }
                                }

                            }
                        }
                    }

                }
            }
            $update = $db->exec('UPDATE intranet_module_package SET status_key = 2 WHERE id = '.$db->quote($row['id'], 'integer'));
            if (PEAR::isError($update)) {
                trigger_error('Error in exec: '.$update->getUserInfo(), E_USER_ERROR);
                exit;
            }
            $package_added += $update;

        }

        // @todo this should not be in the errorlog
        //       it should be somewhere else.
        $details = array(
                'date' => date('r'),
                'type' => 'AccessUpdate',
                'message' => 'AccessUpdate successfully run. '.$package_added.' package(s) added, '.$package_removed.' package(s) removed!',
                'file' => 'ModulePackage/AccessUpdate.php',
                'line' => '[void]');

        require_once("ErrorHandler/Observer/File.php");
        $logger = new ErrorHandler_Observer_File(ERROR_LOG);
        $logger->update($details);

        return true;

    }
}
