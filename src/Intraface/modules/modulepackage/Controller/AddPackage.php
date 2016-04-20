<?php
class Intraface_modules_modulepackage_Controller_AddPackage extends k_Component
{
    protected $template;
    protected $modulepackagemanager;
    protected $modulepackage;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        $this->modulepackage = new Intraface_modules_modulepackage_ModulePackage(intval($this->name()));
        $this->modulepackagemanager = new Intraface_modules_modulepackage_Manager($this->getKernel()->intranet);
        if ($this->modulepackage->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $module->includeFile('ShopExtension.php');
        $module->includeFile('ActionStore.php');

        $add_type = $this->modulepackagemanager->getAddType($this->modulepackage);
        $modulepackageshop = new Intraface_modules_modulepackage_ShopExtension();

        $data = array(
            'modulepackagemanager' => $this->modulepackagemanager,
            'add_type' => $add_type,
            'modulepackageshop' => $modulepackageshop,
            'modulepackage' => $this->modulepackage,
            'kernel' => $this->getKernel());
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/add-package');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->getKernel()->module('modulepackage');
        $module->includeFile('Manager.php');
        $module->includeFile('ShopExtension.php');
        $module->includeFile('ActionStore.php');

        $add_type = $this->modulepackagemanager->getAddType($this->modulepackage);
        $values = $this->body();
        if (!$this->getKernel()->intranet->address->validate($values) || !$this->getKernel()->intranet->address->save($values)) {
            // Here we need to know the errors from address, but it does not validate now!
            $this->modulepackagemanager->error->set('there was an error in your address informations');
            $this->modulepackagemanager->error->merge($this->getKernel()->intranet->address->error->getMessage());
        } else {
            if ($this->body('accept_conditions') != '1') {
                $this->modulepackagemanager->error->set('You need to accept the conditions of sale and use');
            } else {
                // we are now ready to create the order.
                switch ($add_type) {
                    case 'add':
                        $action = $this->modulepackagemanager->add($this->modulepackage, (int)$_POST['duration_month'].' month');
                        break;
                    case 'extend':
                        $action = $this->modulepackagemanager->extend($this->modulepackage, (int)$_POST['duration_month'].' month');
                        break;
                    case 'upgrade':
                        $action = $this->modulepackagemanager->upgrade($this->modulepackage, (int)$_POST['duration_month'].' month');
                        break;
                    default:
                        throw new Exception('Invalid add_type "'.$add_type.'"');
                }

                if (!$this->modulepackagemanager->error->isError()) {
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
                        if (!$action->placeOrder($contact)) {
                            throw new Exception("Unable to place the order");
                        }

                        $total_price = $action->getTotalPrice();
                    } else {
                        $total_price = 0;
                    }

                    // sets private key to be saved.
                    $action->setIntranetPrivateKey($this->getKernel()->intranet->get('private_key'));

                    if (!$action_store_identifier = $action_store->store($action)) {
                        throw new Exception("Unable to store Action!");
                    }

                    // TODO: What do we do if the onlinepayment is not running?

                    // Notice: Only continue to the payment page if price > 0 -- otherwise continue to the process page
                    if (!empty($action_store_identifier) && $total_price > 0) {
                        return new k_SeeOther($this->url('../../payment', array('action_store_identifier' => $action_store_identifier)));
                    } elseif (!empty($action_store_identifier)) {
                        return new k_SeeOther($this->url('../../process', array('action_store_identifier' => $action_store_identifier)));
                    } else {
                        throw new Exception('We did not end up having an action store id!');
                    }
                }
            }
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
