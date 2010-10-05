<?php
/**
 * Signup
 *
 * @todo Kunne let laves s� man kunne oprette sig p� et intranet, man havde f�et lov til med en eller anden kode.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_Signup extends k_Component
{
    public $msg = '';
    public $errors = array();
    protected $kernel;
    protected $template;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
        $this->template = $template;
    }

    function execute()
    {
        $this->url_state->init("continue", $this->url('/login'));
        return parent::execute();
    }

    function renderHtml()
    {
        $this->document->setTitle('Signup');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/signup');
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
            $this->error[] = 'E-mail ugyldig';
        }
        if (!Validate::string($this->body('password'), VALIDATE_ALPHA . VALIDATE_NUM)) {
            $this->error[] = 'Password ugyldigt';
        }
        if (!empty($error) AND count($error) > 0) {
            $this->msg = 'Vi kunne ikke oprette dig';
            return $this->render();
        } else {
            $db = $this->mdb2;
            $res = $db->query("SELECT id FROM user WHERE email = ".$db->quote($this->body('email'), 'text'));
            if (PEAR::isError($res)) {
                throw new Exception($res->getMessage());
            }
            if ($res->numRows() == 0) {
                $res = $db->query("INSERT INTO user SET email = ".$db->quote($this->body('email'), 'text').", password=".$db->quote(md5($this->body('password')), 'text'));
                $user_id = $db->lastInsertID('user');
            } else {
                $this->error[] = 'Du er allerede oprettet';
            }

            if (!empty($error) AND count($error) > 0) {
                $this->msg = 'Du er allerede oprettet. <a href="'.url('/login').'">Login</a>.';
                return $this->render();
            } else {
                require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
                $intranet = new IntranetMaintenance();
                $data = array('identifier' => $this->body('identifier'), 'name' => $this->body('name'));
                if (!$intranet->save($data)) {
                    $this->msg = $intranet->error->view();
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
                                throw new Exception($res->getUserInfo());
                                $this->error[] = 'Kunne ikke oprette nogle af rettighederne';
                            }
                        }
                    }
                    $user = new Intraface_User($user_id);
                    $user->setActiveIntranetId($intranet_id);

                    return new k_SeeOther($this->url('../login'));
                }
            }
        }
        return $this->render();
    }
}
