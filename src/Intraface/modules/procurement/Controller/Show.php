<?php
class Intraface_modules_procurement_Controller_Show extends k_Component
{
    protected $template;
    public $method = 'put';

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }
    function renderHtml()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');
        $translation = $this->getKernel()->getTranslation('procurement');

        $procurement = new Procurement($this->getKernel(), $this->name());
        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $procurement->get('id'));

        # set status
        if (isset($_GET['status'])) {
            $procurement->setStatus($_GET['status']);
        }

        # slet item
        if (isset($_GET['delete_item_id'])) {
            $procurement->loadItem((int)$_GET['delete_item_id']);
            $procurement->item->delete();
        }

        # tilføj kontakt
        if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $module_procurement->getPath()."view.php?id=".$procurement->get('id'));
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                if ($procurement->get('contact_id') != 0) {
                    return new k_SeeOther($url."&contact_id=".$procurement->get('contact_id'));
                } else {
                    return new k_SeeOther($url);
                }
            }
            else {
                trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
            }
        }


        # slet bilag
        if (isset($_GET['delete_appended_file_id'])) {
            $append_file->delete((int)$_GET['delete_appended_file_id']);
        }

        # tilføj produkt
        if (isset($_GET['add_item'])) {
            if ($this->getKernel()->user->hasModuleAccess('product')) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_product = $this->getKernel()->useModule('product');
                $url = $redirect->setDestination($module_product->getPath().'select_product.php?set_quantity=1', $module_procurement->getPath().'set_purchase_price.php?id='.$procurement->get('id'));
                $redirect->askParameter('product_id', 'multiple');
                return new k_SeeOther($url);
            } else {
                trigger_error('You need access to the product module to do this!', E_USER_ERROR);
                exit;
            }
        }

        #retur
        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
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
                    trigger_error('You need access to the contact module!', E_USER_ERROR);
                    exit;
                }
            } elseif ($redirect->get('identifier') == 'file_handler') {

                $file_handler_id = $redirect->getParameter('file_handler_id');
                foreach ($file_handler_id as $id) {
                    $append_file->addFile(new FileHandler($this->getKernel(), $id));
                }

            }
        }

        $data = array('procurement' => $procurement, 'kernel' => $this->getKernel(), 'append_file' => $append_file, 'filehandler' => $filehandler);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/show');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $module = $this->getKernel()->module("procurement");
        $translation = $this->getKernel()->getTranslation('procurement');
        $procurement = new Procurement($this->getKernel(), intval($_GET["id"]));
        $values = $procurement->get();
        $title = "Ret indkøb";

        $this->document->addScript($this->url('procurement/edit.js'));
        $data = array();
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/show');
        return $tpl->render($this, $data);
    }

    function putForm()
    {
        $procurement = new Procurement($this->getKernel(), intval($this->name()));

        if ($procurement->update($_POST)) {

            if (isset($_POST["recieved"]) && $_POST["recieved"] == "1") {
                $procurement->setStatus("recieved");
            }

            return new k_SeeOther($this->url());
        } else {
            $values = $_POST;
            $title = "Ret indkøb";
        }

        return $this->render();
    }

    function postMultipart()
    {
        $module_procurement = $this->getKernel()->module("procurement");
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');
        $translation = $this->getKernel()->getTranslation('procurement');

        $procurement = new Procurement($this->getKernel(), $this->name());
        $filehandler = new FileHandler($this->getKernel());
        $append_file = new AppendFile($this->getKernel(), 'procurement_procurement', $procurement->get('id'));

        # set betalt
        if (isset($_POST['dk_paid_date'])) {
            $procurement->setPaid($_POST['dk_paid_date']);
            if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                header('location: state.php?id=' . intval($procurement->get("id")));
                exit;
            }
        }

        // tilføj bilag med redirect til filemanager
        if (isset($_POST['append_file_choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            $redirect = new Intraface_Redirect($this->getKernel());
            $module_filemanager = $this->getKernel()->useModule('filemanager');
            $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_procurement->getPath().'view.php?id='.$procurement->get('id'));
            $redirect->setIdentifier('file_handler');
            $redirect->askParameter('file_handler_id', 'multiple');
            header("Location: ".$url);
            exit;
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

            }
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}