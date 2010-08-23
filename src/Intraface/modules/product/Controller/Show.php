<?php
class Intraface_modules_product_Controller_Show extends k_Component
{
    protected $error;
    protected $product;
    protected $product_doctrine;
    protected $template;
    protected $doctrine_connection;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, Doctrine_Connection_Common $connection, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->doctrine_connection = $connection;
        $this->mdb2 = $mdb2;
    }

    function map($name)
    {
        if ($name == 'selectvariation') {
            return 'Intraface_modules_product_Controller_Selectproductvariation';
        } elseif ($name == 'filehandler') {
            return 'Intraface_Filehandler_Controller_Index';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        } elseif ($name == 'related') {
            return 'Intraface_modules_product_Controller_Related';
        } elseif ($name == 'shop') {
            return 'Intraface_modules_shop_Controller_Index';
        } elseif ($name == 'stock') {
            // @todo check whether product is stock product
            return 'Intraface_modules_stock_Controller_Product';
        } elseif ($name == 'variations' or $name = 'variation') {
            return 'Intraface_modules_product_Controller_Show_Variations';
        }
    }

    function dispatch()
    {
        if ($this->getProduct()->getId() == 0) {
            throw new k_PageNotFound();
        }

        return parent::dispatch();
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $this->getKernel()->module('product');
        $this->getKernel()->useModule('filemanager');
        $product = new Product($this->getKernel(), $this->name());
        $filehandler = new FileHandler($this->getKernel());
        $data = array(
            'gateway' => $this->getGateway(),
            'product' => $product,
            'kernel' => $this->getKernel(),
            'filehandler' => $filehandler
        );
        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($kernel, 'return');
            if ($redirect->get('identifier') == 'product') {
                $append_file = new Appender($kernel, 'product', $product->get('id'));
                $array_files = $redirect->getParameter('file_handler_id');
                if (is_array($array_files)) {
                    foreach ($array_files AS $file_id) {
                        $append_file->addFile(new FileHandler($kernel, $file_id));
                    }
                }

            }
            return new k_SeeOther($this->url());
        } elseif (isset($_GET['remove_appended_category']) && $this->getKernel()->user->hasModuleAccess('shop')) {
            $product = new Product($this->getKernel(), $this->name());
            $category = new Intraface_Category($this->getKernel(), $this->mdb2, new Intraface_Category_Type('shop', $_GET['shop_id']), $_GET['remove_appended_category']);
            $appender = $category->getAppender($product->getId());
            $appender->delete($category);
            return new k_SeeOther($this->url());
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/show');
        return $smarty->render($this, $data);
    }

    function postMultipart()
    {
        $module = $this->getKernel()->useModule('filemanager');
        $product = new Product($this->getKernel(), $this->name());

        if (isset($_POST['append_file_submit'])) {

            $append_file = $this->getFileAppender();

            if (isset($_FILES['new_append_file'])) {

                $filehandler = new FileHandler($this->getKernel());

                $filehandler->createUpload();
                $filehandler->upload->setSetting('max_file_size', 5000000);

                // @todo: It is not enough validation if we have shop to make it public. Should probably be possible to set on the image if it should be public.
                if ($this->getKernel()->user->hasModuleAccess('shop')) { // if shown i webshop $product->get('do_show') == 1
                    $filehandler->upload->setSetting('file_accessibility', 'public');
                }
                if ($id = $filehandler->upload->upload('new_append_file')) {
                    if (!$append_file->addFile(new FileHandler($this->getKernel(), $id))) {
                        throw new Exception('Could not add file');
                    }

                }
            }
            if (!$filehandler->error->isError()) {
                return new k_SeeOther($this->url());
            }

        }

        if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $module_filemanager = $this->getKernel()->useModule('filemanager');
            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('filehandler/selectfile', array('images'=>1, 'multiple_choice' => 1)), NET_SCHEME . NET_HOST . $this->url());
            $redirect->setIdentifier('product');
            $redirect->askParameter('file_handler_id', 'multiple');

            return new k_SeeOther($url);
        }
        return $this->render();
    }

    function renderHtmlEdit()
    {
        $this->getKernel()->module('product');
        $this->getKernel()->useModule('filemanager');
        $translation = $this->getKernel()->getTranslation('product');

        $data = array(
            'product' => $this->getProductDoctrine(),
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/edit');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
        if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
            return new k_SeeOther($this->url('filehandler/selectfile'));
        }

        //$product = $this->getProductDoctrine();

        $this->getProductDoctrine()->getDetails()->number = $_POST['number'];
        $this->getProductDoctrine()->getDetails()->Translation['da']->name = $_POST['name'];
        $this->getProductDoctrine()->getDetails()->Translation['da']->description = $_POST['description'];
        $this->getProductDoctrine()->getDetails()->price = new Ilib_Variable_Float($_POST['price'], 'da_dk');
        if (isset($_POST['before_price'])) {
            $this->getProductDoctrine()->getDetails()->before_price = new Ilib_Variable_Float($_POST['before_price'], 'da_dk');
        }
        if (isset($_POST['weight'])) {
            $this->getProductDoctrine()->getDetails()->weight = new Ilib_Variable_Float($_POST['weight'], 'da_dk');
        }
        if (isset($_POST['unit'])) {
            $this->getProductDoctrine()->getDetails()->unit = $_POST['unit'];
        }
        if (isset($_POST['vat'])) {
            $this->getProductDoctrine()->getDetails()->vat = $_POST['vat'];
        }
        if (isset($_POST['do_show'])) {
            $this->getProductDoctrine()->do_show = $_POST['do_show'];
        }
        if (isset($_POST['state_account_id'])) {
            $this->getProductDoctrine()->getDetails()->state_account_id = (int)$_POST['state_account_id'];
        }
        if (isset($_POST['has_variation'])) {
            $this->getProductDoctrine()->has_variation = $_POST['has_variation'];
        }
        if (isset($_POST['stock'])) {
            $this->getProductDoctrine()->stock = $_POST['stock'];
        }
        //print_r($product->asArray(true)); die('AA');
        try {
            $this->getProductDoctrine()->save();

            if ($redirect->get('id') != 0) {
                $redirect->setParameter('product_id', $this->getProductDoctrine()->getId());
            }
            return new k_SeeOther($this->url());
        } catch (Doctrine_Validator_Exception $e) {
            //$this->product_doctrine = $product;
            $this->getError()->attachErrorStack($this->getProductDoctrine()->getCollectedErrorStack());
        }

        return $this->render();
    }

    function renderHtmlDelete()
    {
        $form = new HTML_QuickForm(null, 'post', $this->url(null, array('delete')));
        $form->addElement('hidden', '_method', 'delete');
        $form->addElement('submit', null, $this->t('Delete'));
        return $form->toHtml();
    }

    function renderHtmlCopy()
    {
        $this->getKernel()->module('product');
        if ($id = $this->getProduct()->copy()) {
            return new k_SeeOther($this->url('../' . $id));
        }
    }

    function DELETE()
    {
        $this->getKernel()->module('product');
        if ($id = $this->getProduct()->delete()) {
            return new k_SeeOther($this->url('../'));
        }
    }

    function getProduct()
    {
        if (is_object($this->product)) {
            return $this->product;
        }

        require_once 'Intraface/modules/product/Product.php';
        return $this->product = new Product($this->getKernel(), $this->name());
    }

    /**
     * Gets the model
     *
     * @see Intraface_Keyword_Controller
     * @see Intraface_Filehandler_Controller
     *
     * @return object
     */
    function getModel()
    {
        return $this->getProduct();
    }

    function getProductDoctrine()
    {
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        if (is_object($this->product_doctrine)) {
            return $this->product_doctrine;
        }
        return ($this->product_doctrine = $this->getGateway()->findById((int)$this->name()));
    }

    function getFileAppender()
    {
        require_once 'Intraface/modules/filemanager/AppendFile.php';

        $this->getKernel()->module('product');
        return new AppendFile($this->getKernel(), 'product', $this->getProduct()->get('id'));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getTranslation()
    {
        return $translation = $this->getKernel()->getTranslation('product');
    }

    function getError()
    {
        if(!is_object($this->error)) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getTranslation());
        }

        return $this->error;
    }

    function getGateway()
    {
        return new Intraface_modules_product_ProductDoctrineGateway($this->doctrine_connection, $this->getKernel()->user);
    }
}