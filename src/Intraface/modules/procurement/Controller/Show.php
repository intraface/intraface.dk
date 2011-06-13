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

    function renderHtml()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $this->getProcurement()->get('id'));

        if ($this->query('status')) {
            $this->getProcurement()->setStatus($this->query('status'));
        } elseif ($this->query('delete_item_id')) {
            $this->getProcurement()->loadItem((int)$this->query('delete_item_id'));
            $this->getProcurement()->item->delete();
        } elseif ($this->query('add_contact') == 1) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('choosecontact'), NET_SCHEME . NET_HOST . $this->url());
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                if ($this->getProcurement()->get('contact_id') != 0) {
                    return new k_SeeOther($url."&contact_id=".$this->getProcurement()->get('contact_id'));
                } else {
                    return new k_SeeOther($url);
                }
            } else {
                throw new Exception("Du har ikke adgang til modulet contact");
            }
        } elseif ($this->query('delete_appended_file_id')) {
            $append_file->delete((int)$this->query('delete_appended_file_id'));
            return new k_SeeOther($this->url());
        } elseif ($this->query('add_item')) {
            if ($this->getKernel()->user->hasModuleAccess('product')) {
                // @TODO This will not work
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_product = $this->getKernel()->useModule('product');
                $url = $redirect->setDestination($module_product->getPath().'select_product.php?set_quantity=1', $module_procurement->getPath().'set_purchase_price.php?id='.$this->getProcurement()->get('id'));
                $redirect->askParameter('product_id', 'multiple');
                return new k_SeeOther($url);
            } else {
                throw new Exception('You need access to the product module to do this!');
            }
        } elseif ($this->query('contact_id')) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');
                $contact = new Contact($this->getKernel(), $this->query('contact_id'));
                if ($contact->get('id') != 0) {
                    $this->getProcurement()->setContact($contact);
                } else {
                    $this->getProcurement()->error->set('Ingen gyldig kontakt blev valgt');
                }
            } else {
                throw new Exception('You need access to the contact module!');
            }
        } elseif ($this->query('from') == 'select_product') {
            return new k_SeeOther($this->url('purchaseprice'));
        }

        $data = array(
        	'procurement' => $this->getProcurement(),
        	'kernel' => $this->getKernel(),
        	'append_file' => $append_file,
        	'filehandler' => $filehandler);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/show');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $module = $this->getKernel()->module("procurement");
        $this->document->setTitle($this->t("Edit procurement"));
        $this->document->addScript('procurement/edit.js');

        $data = array(
        	'procurement' => $this->getProcurement(),
        	'kernel' => $this->getKernel(),
        	'title' => $this->t("Edit procurement"),
            'gateway' => new Intraface_modules_procurement_ProcurementGateway($this->getKernel()),
            'values' => $this->getValues());
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/procurement-edit');
        return $tpl->render($this, $data);
    }

    function putForm()
    {
        if ($this->getProcurement()->update($this->body())) {
            if ($this->body("recieved") == "1") {
                $this->getProcurement()->setStatus("recieved");
            }
            return new k_SeeOther($this->url());
        }

        return $this->render();
    }

    function postForm()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        if ($this->body('dk_paid_date')) {
            $this->getProcurement()->setPaid($this->body('dk_paid_date'));
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

        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $this->getProcurement()->get('id'));

        if ($this->body('dk_paid_date')) {
            $procurement->setPaid($this->body('dk_paid_date'));
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                return new k_SeeOther($this->url('state'));
            }
        }

        if ($this->body('append_file_choose_file') && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            return new k_SeeOther($this->url('filehandler/selectfile', array('multiple_choice' => true)));
        }

        // upload voucher
        if ($this->body('append_file_submit')) {
            if (isset($_FILES['new_append_file'])) {
                $filehandler->createUpload();
                $filehandler->upload->setSetting('max_file_size', '2000000');
                if ($id = $filehandler->upload->upload('new_append_file')) {
                    $append_file->addFile(new FileHandler($this->getKernel(), $id));
                } else {
                    $this->getProcurement()->error->merge($filehandler->error->getMessage());
                    return $this->render();
                }
                return new k_SeeOther($this->url());
            }
        }

        return $this->render();
    }

    function getValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return $this->getProcurement()->get();
    }

    function appendFile($file_id)
    {
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $this->getProcurement()->get('id'));
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
        $this->getProcurement()->loadItem();
        $this->getProcurement()->item->save(array(
        	'product_id' => $product['product_id'],
        	'product_variation_id' => $product['product_variation_id'],
        	'quantity' => intval($quantity)));
    }
}