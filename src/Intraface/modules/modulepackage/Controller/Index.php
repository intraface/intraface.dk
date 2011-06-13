<?php
class Intraface_modules_modulepackage_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'package') {
            return 'Intraface_modules_modulepackage_Controller_Package';
        } elseif ($name == 'payment') {
            return 'Intraface_modules_modulepackage_Controller_Payment';
        } elseif ($name == 'process') {
            return 'Intraface_modules_modulepackage_Controller_Process';
        } elseif ($name == 'postform') {
            return 'Intraface_modules_modulepackage_Controller_Postform';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $modulepackagemanager = null;

        if (intval($this->query('unsubscribe_id')) != 0) {
            $modulepackagemanager = new Intraface_modules_modulepackage_Manager($this->getKernel()->intranet, (int)$_GET['unsubscribe_id']);
            if ($modulepackagemanager->get('id') != 0) {
                if ($modulepackagemanager->get('status') == 'created') {
                    $modulepackagemanager->delete();
                } elseif ($modulepackagemanager->get('status') == 'active') {
                    $modulepackagemanager->terminate();

                    $module->includeFile('AccessUpdate.php');
                    $access_update = new Intraface_modules_modulepackage_AccessUpdate();
                    $access_update->run($this->getKernel()->intranet->get('id'));
                    $this->getKernel()->user->clearCachedPermission();
                } else {
                    $modulepackagemanager->error->set('it is not possible to unsubscribe module packages which is not either created or active');
                }
            }
        }

        $modulepackagemanager = new Intraface_modules_modulepackage_Manager($this->getKernel()->intranet);
        $modulepackagemanager->getDBQuery($this->getKernel())->setFilter('status', 'created_and_active');
        $packages = $modulepackagemanager->getList();

        $data = array('packages' => $packages, 'modulepackagemanager' => $modulepackagemanager);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}