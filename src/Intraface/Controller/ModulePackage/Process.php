<?php
class Intraface_Controller_ModulePackage_Process extends k_Component
{
    protected $kernel;
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
    }

    function postForm()
    {
        // When we recieve from Quickpay payment not being logged in
        $action = Intraface_modules_modulepackage_ActionStore::restoreFromIdentifier($this->mdb2, $_GET['action_store_identifier']);
        if (!$action) {
            throw new Exception('Unable to restore action from identifier '. $_GET['action_store_identifier']);
        }

        // We login to the intranet with the private key
        $adapter = new Intraface_Auth_PrivateKeyLogin($this->mdb2, uniqid(), $action->getIntranetPrivateKey());
        $weblogin = $adapter->auth();
        if (!$intranet_id = $weblogin->getActiveIntranetId()) {
            throw new Exception("Unable to log in to the intranet with public key: ".$action->getIntranetPrivateKey());
        }

        $this->getKernel()->weblogin = $weblogin;
        $this->getKernel()->intranet = new Intraface_Intranet($intranet_id);
        $this->getKernel()->setting = new Intraface_Setting($this->getKernel()->intranet->get('id'));

        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $module->includeFile('ShopExtension.php');
        $module->includeFile('ActionStore.php');
        $module->includeFile('AccessUpdate.php');

        $shop = new Intraface_modules_modulepackage_ShopExtension();
        $order = $shop->getOrderDetails($action->getOrderIdentifier());

        if (empty($order)) {
            throw new Exception('Unable to restore order from identifier '.$action->getOrderIdentifier());
        }

        $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
        $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
        $payment_postprocess = $payment_authorize->getPostProcess($_GET, $_POST, $_SESSION, $order);

        $amount = $payment_postprocess->getAmount();

        $shop->addPaymentToOrder($action->getOrderIdentifier(), $payment_postprocess);

        if ($payment_postprocess->getPbsStatus() == '000') {
            if ($amount >= $action->getTotalPrice()) {
                if ($action->execute($this->getKernel()->intranet)) {
                    // we delete the action from the store
                    $action_store = new Intraface_modules_modulepackage_ActionStore($this->getKernel()->intranet->get('id'));
                    $action_store->restore($_GET['action_store_identifier']);
                    $action_store->delete();

                    // TODO: do we maybe want to send an email to the customer?

                    $access_update = new Intraface_modules_modulepackage_AccessUpdate();
                    $access_update->run($this->getKernel()->intranet->get('id'));
                    return new k_TextResponse('SUCCESS!');
                } else {
                    $response = new k_TextResponse('Failure:'.$action->error->view());
                    $response->setStatus(400);
                    return $response;
                }
            } else {
                // TODO: Here we can send an e-mail that says they still need to pay some more OR?
                throw new Exception('Failure: Not sufficient payment');
            }
        } else {
            // @todo should throw a 401
            $response = new k_TextResponse('Payment attempt registered. Not authorized');
            $response->setStatus(401);
            return $response;
        }
        $response = new k_TextResponse('Unknown failure');
        $response->setStatus(400);
        return $response;
    }

    function getKernel()
    {
        if (is_object($this->kernel)) {
            return $this->kernel;
        }
        return $this->kernel = new Intraface_Kernel();
    }
}
