<?php
/**
 * Signup
 *
 * @todo Kunne let laves så man kunne oprette sig på et intranet, man havde fået lov til med en eller anden kode.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_Signup extends k_Component
{
    protected $registry;
    protected $kernel;
    public $msg;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function execute()
    {
        $this->url_state->init("continue", $this->url('/login'));
        return parent::execute();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/signup.tpl.php');
        return $smarty->render($this);
    }

    /*
    function getKernel()
    {
        if (is_object($this->kernel)) {
            return $this->kernel;
        }
        $registry = $this->registry->create();
    	return $this->kernel = $registry->get('kernel');
    }
    */

    function postForm()
    {
        if (!Validate::email($this->body('email'))) {
            $error[] = 'E-mail ugyldig';
        }
        if (!Validate::string($this->body('password'), VALIDATE_ALPHA . VALIDATE_NUM)) {
            $error[] = 'Password ugyldigt';
        }
        if (!empty($error) AND count($error) > 0) {
            $msg = 'Vi kunne ikke oprette dig';
            return new k_seeOther($this->url());
        } else {
            $db = MDB2::singleton(DB_DSN);
            $res = $db->query("SELECT id FROM user WHERE email = ".$db->quote($this->body('email'), 'text'));
            if (PEAR::isError($res)) {
                trigger_error($res->getMessage(), E_USER_ERROR);
            }
            if ($res->numRows() == 0) {
                $res = $db->query("INSERT INTO user SET email = ".$db->quote($this->body('email'), 'text').", password=".$db->quote(md5($this->body('password')), 'text'));
                $user_id = $db->lastInsertID('user');
            } else {
                $error[] = 'Du er allerede oprettet';
            }

            if (!empty($error) AND count($error) > 0) {
                $content->msg = 'Du er allerede oprettet. <a href="'.url('/login').'">Login</a>.';
            } else {
                require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
                $intranet = new IntranetMaintenance();
                $data = array('identifier' => $this->body('identifier'), 'name' => $this->body('name'));
                if (!$intranet->save($data)) {
                    $content->msg = $intranet->error->view();
                } else {
                    $intranet_id = $intranet->getId(); // betatest intranet for forskellige brugere

                    // intranet access
                    $db->query("INSERT INTO permission SET intranet_id = ".$db->quote($intranet_id, 'integer').", user_id = ".$db->quote($user_id, 'integer'));

                    // module access
                    $modules = array('administration', 'modulepackage', 'onlinepayment', 'cms', 'filemanager', 'contact', 'debtor','quotation', 'invoice', 'order','accounting', 'product', 'stock', 'webshop');

                    foreach ($modules AS $module) {
                        $res = $db->query("SELECT id FROM module WHERE name = ".$db->quote($module, 'text')." LIMIT 1");
                        if ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                            $db->query("INSERT INTO permission SET
                                intranet_id = ".$db->quote($intranet_id, 'integer').",
                                user_id = ".$db->quote($user_id, 'integer').",
                                module_id = ".$db->quote($row['id'], 'integer'));
                            $db->query("INSERT INTO permission SET
                                intranet_id = ".$db->quote($intranet_id, 'integer').",
                                user_id = ".$db->quote(0, 'integer').",
                                module_id = ".$db->quote($row['id'], 'integer'));
                        }


                    }

                    $sub_access = array('edit_templates', 'setting', 'vat_report', 'endyear');

                    foreach ($sub_access AS $module) {
                        $res = $db->query("SELECT id, module_id FROM module_sub_access WHERE name = ".$db->quote($module, 'text')." LIMIT 1");
                        if ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {

                            $res = $db->query("INSERT INTO permission SET intranet_id = ".$db->quote($intranet_id, 'integer').", module_sub_access_id = ".$db->quote($row['id'], 'integer').", module_id = ".$db->quote($row['module_id'], 'integer').", user_id = ".$db->quote($user_id, 'integer'));
                            if (PEAR::isError($res)) {
                                trigger_error('Kunne ikke oprette nogle af rettighederne', E_USER_ERROR);
                            }
                        }
                    }
                    $user = new Intraface_User($user_id);
                    $user->setActiveIntranetId($intranet_id);

                    return new k_SeeOther($this->url('../login'));
                }
            }
            return $this->render();
        }
    }

    function t($phrase)
    {
        return $phrase;
    }
}
