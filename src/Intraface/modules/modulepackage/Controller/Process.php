<?php
class Intraface_modules_modulepackage_Controller_Process extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        // Here we are logged in so we can use the normal way to acccess files.

        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $module->includeFile('ShopExtension.php');
        $module->includeFile('ActionStore.php');
        $module->includeFile('AccessUpdate.php');

        // When there is no payment we get this from add_package.php
        $identifier = $_GET['action_store_identifier'];

        $action_store = new Intraface_modules_modulepackage_ActionStore($this->getKernel()->intranet->get('id'));
        $action = $action_store->restore($identifier);

        if (!is_object($action)) {
            throw new Exception("Problem restoring action from identifier ".$identifier);
        }

        // We make a double check
        if ($action->hasAddActionWithProduct() && $action->getTotalPrice() > 0) {
            throw new Exception("The actions can not be processed without payment!");
        }

        if ($action->execute($this->getKernel()->intranet)) {
            // we delete the action from the store
            $action_store->delete();

            $access_update = new Intraface_modules_modulepackage_AccessUpdate();
            $access_update->run($this->getKernel()->intranet->get('id'));
            return new k_SeeOther($this->url('../', array('status' => 'success')));
        }

        // TODO: we need to find a better solution for this
        $response = new k_TextResponse('Failure: ' . $action->error->view());
        $response->setStatus(400);
        return $response;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}