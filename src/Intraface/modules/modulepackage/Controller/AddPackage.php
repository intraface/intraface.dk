<?php
class Intraface_modules_modulepackage_Controller_AddPackage extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $module->includeFile('ShopExtension.php');
        $module->includeFile('ActionStore.php');

        $translation = $this->getKernel()->getTranslation('modulepackage');
        $modulepackage = new Intraface_modules_modulepackage_ModulePackage(intval($_GET['id']));
        $modulepackagemanager = new Intraface_modules_modulepackage_Manager($this->getKernel()->intranet);
        if ($modulepackage->get('id') == 0) {
            trigger_error("Invalid id", E_USER_ERROR);
            exit;
        }

        $add_type = $modulepackagemanager->getAddType($modulepackage);
        $modulepackageshop = new Intraface_modules_modulepackage_ShopExtension();

        $data = array('modulepackagemanager' => $modulepackagemanager);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/add-package');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {

        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $module->includeFile('ShopExtension.php');
        $module->includeFile('ActionStore.php');

        $translation = $this->getKernel()->getTranslation('modulepackage');
        $modulepackage = new Intraface_modules_modulepackage_ModulePackage(intval($_POST['id']));
        $modulepackagemanager = new Intraface_modules_modulepackage_Manager($this->getKernel()->intranet);

        $add_type = $modulepackagemanager->getAddType($modulepackage);

        $values = $_POST;
        if (!$this->getKernel()->intranet->address->validate($values) || !$this->getKernel()->intranet->address->save($values)) {
            // Here we need to know the errors from address, but it does not validate now!
            $modulepackagemanager->error->set('there was an error in your address informations');
            $modulepackagemanager->error->merge($this->getKernel()->intranet->address->error->getMessage());
        }
        else {
            if (!isset($_POST['accept_conditions']) || $_POST['accept_conditions'] != '1') {
                $modulepackagemanager->error->set('You need to accept the conditions of sale and use');
            }
            else {

                // we are now ready to create the order.
                switch($add_type) {
                    case 'add':
                        $action = $modulepackagemanager->add($modulepackage, (int)$_POST['duration_month'].' month');
                        break;
                    case 'extend':
                        $action = $modulepackagemanager->extend($modulepackage, (int)$_POST['duration_month'].' month');
                        break;
                    case 'upgrade':
                        $action = $modulepackagemanager->upgrade($modulepackage, (int)$_POST['duration_month'].' month');
                        break;
                    default:
                        trigger_error('Invalid add_type "'.$add_type.'"', E_USER_ERROR);
                        exit;
                }

                if (!$modulepackagemanager->error->isError()) {
                    $action_store = new Intraface_modules_modulepackage_ActionStore($this->getKernel()->intranet->get('id'));
                    if ($action->hasAddActionWithProduct()) {

                        $contact = $this->getKernel()->intranet->address->get();
                        // The following we do not want to transfer as this can give problems.
                        unset($contact['id']);
                        unset($contact['type']);
                        unset($contact['belong_to_id']);
                        unset($contact['address_id']);

                        // If the intranet address is different from the user it is probably a company.
                        if ($this->getKernel()->intranet->address->get('name') != $this->getKernel()->user->getAddress()->get('name')) {
                            $contact['contactperson'] = $this->getKernel()->user->getAddress()->get('name');
                            $contact['contactemail'] = $this->getKernel()->user->getAddress()->get('email');
                            $contact['contactphone'] =  $this->getKernel()->user->getAddress()->get('phone');
                        }

                        // We add the contact_id. But notice, despite of the bad naming the contact_id is the contact_id in the intranet_maintenance intranet!
                        $contact['contact_id'] = (int)$this->getKernel()->intranet->get('contact_id');

                        // we place the order.
                        if (!$action->placeOrder($contact, Intraface_Mail::factory())) {
                            trigger_error("Unable to place the order", E_USER_ERROR);
                            exit;
                        }

                        $total_price = $action->getTotalPrice();

                    }
                    else {
                        $total_price = 0;
                    }

                    // sets private key to be saved.
                    $action->setIntranetPrivateKey($this->getKernel()->intranet->get('private_key'));

                    if (!$action_store_identifier = $action_store->store($action)) {
                        trigger_error("Unable to store Action!", E_USER_ERROR);
                        exit;
                    }

                    // TODO: What do we do if the onlinepayment is not running?

                    // Notice: Only if the price is more than zero we continue to the payment page, otherwise we contibue to the process page further down.
                    if (!empty($action_store_identifier) && $total_price > 0) {
                        header('location: payment.php?action_store_identifier='.$action_store_identifier);
                        exit;
                    }
                    elseif (!empty($action_store_identifier)) {
                        header('location: process.php?action_store_identifier='.$action_store_identifier);
                        exit;
                    }
                    else {
                        trigger_error('We did not end up having an action store id!', E_USER_ERROR);
                        exit;
                    }
                }
            }
        }

    }
}
