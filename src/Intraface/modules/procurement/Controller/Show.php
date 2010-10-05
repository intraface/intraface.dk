<?php
class Intraface_modules_procurement_Controller_Show extends k_Component
{
    protected $template;
    public $method = 'put';
    protected $error;
    protected $procurement;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ('choosecontact' == $name) {
            return 'Intraface_modules_contact_Controller_Choosecontact';
        } elseif ('filehandler' == $name) {
            return 'Intraface_Filehandler_Controller_Index';
        } elseif ('selectproduct' == $name) {
            return 'Intraface_modules_product_Controller_Selectproduct';
        } elseif ('state' == $name) {
            return 'Intraface_modules_accounting_Controller_State_Procurement';
        } elseif ($name == 'purchaseprice') {
            return 'Intraface_modules_procurement_Controller_PurchasePrice';
        } elseif ($name == 'item') {
            return 'Intraface_modules_procurement_Controller_Items';
        }
    }

    function dispatch()
    {
        if ($this->getProcurement()->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function appendFile($file_id)
    {
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $procurement = new Procurement($this->getKernel(), $this->name());
        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $procurement->get('id'));
        $append_file->addFile(new FileHandler($this->getKernel(), $file_id));
        return true;
    }

    function getProcurement()
    {
        if (is_object($this->procurement)) {
            return $this->procurement;
        }
        return ($this->procurement = $this->context->getProcurementGateway()->findById($this->name()));
    }

    /**
     * Used by state
     *
     * @see Intraface_modules_accounting_Controller_State_Procurement
     *
     * @return object
     */
    function getModel()
    {
        return $this->getProcurement();
    }

    function getError()
    {
        return $this->getProcurement()->error;
    }

    function renderHtml()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $procurement = $this->getProcurement();
        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $procurement->get('id'));

        if (isset($_GET['status'])) {
            $procurement->setStatus($_GET['status']);
        } elseif (isset($_GET['delete_item_id'])) {
            $procurement->loadItem((int)$_GET['delete_item_id']);
            $procurement->item->delete();
        } elseif (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('choosecontact'), NET_SCHEME . NET_HOST . $this->url());
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                if ($procurement->get('contact_id') != 0) {
                    return new k_SeeOther($url."&contact_id=".$procurement->get('contact_id'));
                } else {
                    return new k_SeeOther($url);
                }
            } else {
                throw new Exception("Du har ikke adgang til modulet contact");
            }
        } elseif (isset($_GET['delete_appended_file_id'])) {
            $append_file->delete((int)$_GET['delete_appended_file_id']);
            return new k_SeeOther($this->url());
        } elseif (isset($_GET['add_item'])) {
            if ($this->getKernel()->user->hasModuleAccess('product')) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_product = $this->getKernel()->useModule('product');
                $url = $redirect->setDestination($module_product->getPath().'select_product.php?set_quantity=1', $module_procurement->getPath().'set_purchase_price.php?id='.$procurement->get('id'));
                $redirect->askParameter('product_id', 'multiple');
                return new k_SeeOther($url);
            } else {
                throw new Exception('You need access to the product module to do this!');
                exit;
            }
        } //elseif (isset($_GET['return_redirect_id'])) {
            //$redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            /*
            if ($redirect->get('identifier') == 'contact') {
                if ($this->getKernel()->user->hasModuleAccess('contact')) {
                    $contact_module = $this->getKernel()->useModule('contact');
                    $contact = new Contact($this->getKernel(), $redirect->getParameter('contact_id'));
                    if ($contact->get('id') != 0) {
                        $procurement->setContact($contact);
                    } else {
                        $procurement->error->set('Ingen gyldig kontakt blev valgt');
                    }

                } else {
                    throw new Exception('You need access to the contact module!');
                    exit;
                }
            }
            */
            /*
            if ($redirect->get('identifier') == 'file_handler') {

                $file_handler_id = $redirect->getParameter('file_handler_id');
                foreach ($file_handler_id as $id) {
                    $append_file->addFile(new FileHandler($this->getKernel(), $id));
                }

            }
            */
        //}
        elseif ($this->query('contact_id')) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');
                $contact = new Contact($this->getKernel(), $this->query('contact_id'));
                if ($contact->get('id') != 0) {
                    $procurement->setContact($contact);
                } else {
                    $procurement->error->set('Ingen gyldig kontakt blev valgt');
                }

            } else {
                throw new Exception('You need access to the contact module!');
                exit;
            }

        } elseif ($this->query('from') == 'select_product') {
            return new k_SeeOther($this->url('purchaseprice'));
        }

        $data = array(
        	'procurement' => $procurement,
        	'kernel' => $this->getKernel(),
        	'append_file' => $append_file,
        	'filehandler' => $filehandler);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/show');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $module = $this->getKernel()->module("procurement");
        $procurement = $this->getProcurement();
        $values = $procurement->get();
        $this->document->setTitle($this->t("Edit procurement"));
        $this->document->addScript('procurement/edit.js');

        $data = array(
        	'procurement' => $procurement,
        	'kernel' => $this->getKernel(),
        	'title' => $this->t("Edit procurement"),
            'gateway' => new Intraface_modules_procurement_ProcurementGateway($this->getKernel()),
            'values' => $values);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/procurement-edit');
        return $tpl->render($this, $data);
    }

    function putForm()
    {
        $procurement = $this->getProcurement();

        if ($procurement->update($_POST)) {

            if (isset($_POST["recieved"]) && $_POST["recieved"] == "1") {
                $procurement->setStatus("recieved");
            }

            return new k_SeeOther($this->url());
        } else {
            $values = $_POST;
        }

        return $this->render();
    }

    function postForm()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $procurement = $this->getProcurement();
        /*
        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $procurement->get('id'));
        */
        if (isset($_POST['dk_paid_date'])) {
            $procurement->setPaid($_POST['dk_paid_date']);
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url('state'));
            }
        }

        return $this->render();
    }

    function postMultipart()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $procurement = $this->getProcurement();
        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $procurement->get('id'));

        if (isset($_POST['dk_paid_date'])) {
            $procurement->setPaid($_POST['dk_paid_date']);
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url('state'));
            }
        }

        if (isset($_POST['append_file_choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            /*
            $redirect = new Intraface_Redirect($this->getKernel());
            $module_filemanager = $this->getKernel()->useModule('filemanager');
            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('selectfile'), NET_SCHEME . NET_HOST . $this->url());
            $redirect->setIdentifier('file_handler');
            $redirect->askParameter('file_handler_id', 'multiple');
            return new k_SeeOther($url);
            */
            return new k_SeeOther($this->url('filehandler/selectfile', array('multiple_choice' => true)));
        }

        // upload billag
        if (isset($_POST['append_file_submit'])) {
            if (isset($_FILES['new_append_file'])) {

                $filehandler->createUpload();
                $filehandler->upload->setSetting('max_file_size', '2000000');
                if ($id = $filehandler->upload->upload('new_append_file')) {
                    $append_file->addFile(new FileHandler($this->getKernel(), $id));
                }
                $procurement->error->merge($filehandler->error->getMessage());
                return new k_SeeOther($this->url());
            }
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getReturnUrl($contact_id)
    {
        return $this->url(null, array('contact_id' => $contact_id));
    }

    function addItem($product, $quantity = 1)
    {
       	$procurement = $this->context->getProcurementGateway()->findById($this->name());
        $procurement->loadItem();
        $procurement->item->save(array(
        	'product_id' => $product['product_id'],
        	'product_variation_id' => $product['product_variation_id'],
        	'quantity' => intval($quantity)));

    }
}