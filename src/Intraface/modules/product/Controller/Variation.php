<?php
class Intraface_modules_product_Controller_Variation extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map()
    {
        return 'Intraface_modules_stock_Controller_Product';
    }

    function getProduct()
    {
        return $this->context->getProduct();
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');

        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');
        $product = new Product($this->getKernel(), $this->context->getProductId());
        $variation = $product->getVariation($this->name());

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($redirect->get('identifier') == 'product') {
                $append_file = new AppendFile($this->getKernel(), 'product', $product->get('id'));
                $array_files = $redirect->getParameter('file_handler_id');
                if (is_array($array_files)) {
                    foreach ($array_files AS $file_id) {
                        $append_file->addFile(new FileHandler($this->getKernel(), $file_id));
                    }
                }

            }
        }

        // this has to be moved to post
        if (isset($_GET['delete_appended_file_id'])) {
            $product = new Product($this->getKernel(), $_GET['id']);
            $append_file = new AppendFile($this->getKernel(), 'product', $product->get('id'));
            $append_file->delete((int)$_GET['delete_appended_file_id']);
            header('Location: product.php?id='.$product->get('id'));
            exit;

        }

        $data = array(
            'product' => $product,
            'variation' => $variation,
            'kernel' => $this->getKernel()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/variation');
        return $tpl->render($this, $data);
    }

    function postMultipart()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');

        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $product = new Product($this->getKernel(), $_POST['id']);

        if (isset($_POST['append_file_submit'])) {

            $filehandler = new FileHandler($this->getKernel());
            $append_file = new AppendFile($this->getKernel(), 'product', $product->get('id'));

            if (isset($_FILES['new_append_file'])) {
                $filehandler = new FileHandler($this->getKernel());

                $filehandler->createUpload();
                if ($product->get('do_show') == 1) { // if shown i webshop
                    $filehandler->upload->setSetting('file_accessibility', 'public');
                }
                if ($id = $filehandler->upload->upload('new_append_file')) {
                    $append_file->addFile(new FileHandler($this->getKernel(), $id));
                }
            }
        }

        if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $module_filemanager = $this->getKernel()->useModule('filemanager');
            $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1', $module->getPath().'product.php?id='.$product->get('id'));
            $redirect->setIdentifier('product');
            $redirect->askParameter('file_handler_id', 'multiple');

            return new k_SeeOther($url);
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}