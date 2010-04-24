<?php
class Intraface_modules_product_Controller_Index extends k_Component
{
    protected $gateway_doctrine;
    protected $gateway;
    protected $product;
    protected $product_doctrine;
    private $error;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_product_Controller_Show';
        } elseif ($name == 'attributegroups') {
            return 'Intraface_modules_product_Controller_AttributeGroups';
        } elseif ($name == 'batchedit') {
            return 'Intraface_modules_product_Controller_BatchEdit';
        }  elseif ($name == 'batchprice') {
            return 'Intraface_modules_product_Controller_BatchPriceChanger';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/index');
        return $smarty->render($this);
    }

    function putForm()
    {
        $gateway = $this->getGateway();
        if (!empty($_POST['action']) AND $_POST['action'] == 'delete') {
            $deleted = array();
            if (!empty($_POST['selected']) AND is_array($_POST['selected'])) {
                foreach ($_POST['selected'] as $key=>$id) {
                    $product = $gateway->getById(intval($id));
                    if ($product->delete()) {
                        $deleted[] = $id;
                    }
                }
            }
        } elseif (!empty($_POST['undelete'])) {
            if (!empty($_POST['deleted']) AND is_string($_POST['deleted'])) {
                $undelete = unserialize(base64_decode($_POST['deleted']));
            } else {
                throw new Exception('could not undelete');
            }
            if (!empty($undelete) AND is_array($undelete)) {
                foreach ($undelete as $key=>$id) {
                    $product = $gateway->getById(intval($id));
                    if (!$product->undelete()) {
                        // void
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

    function getProductDoctrine()
    {
        if (is_object($this->product_doctrine)) {
            return $this->product_doctrine;
        }

        return $this->product_doctrine = new Intraface_modules_product_ProductDoctrine;
    }

    function getProduct()
    {
        if (is_object($this->product)) {
            return $this->product;
        }

        require_once 'Intraface/modules/product/Product.php';
        return $this->product = new Product($this->getKernel());
    }

    function getGateway()
    {
        if (empty($this->gateway)) {
            $this->gateway = new Intraface_modules_product_Gateway($this->getKernel());
        }
        return $this->gateway;
    }

    function getKeywords()
    {
        $gateway = $this->getGateway();
        $product = $gateway->getById(0);
        // $characters = $product->getCharacters();
        return $keywords = $product->getKeywordAppender();
    }

    function getError()
    {
        if(!is_object($this->error)) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getTranslation());
        }

        return $this->error;
    }

    function getProducts()
    {
        // $gateway = $this->factory->create($this->getKernel());
        $gateway = $this->getGateway();

        $product = $gateway->getById(0);
        // $characters = $product->getCharacters();
        $keywords = $product->getKeywordAppender();

        // burde bruge query
        if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {
            if (isset($_GET["search"])) {
                $gateway->getDBQuery()->setFilter("search", $_GET["search"]);
            }

            if (isset($_GET["keyword_id"])) {
                $gateway->getDBQuery()->setKeyword($_GET["keyword_id"]);
            }
        } else {
            $gateway->getDBQuery()->useCharacter();
        }

        $gateway->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $gateway->getDBQuery()->usePaging("paging");
        $gateway->getDBQuery()->storeResult("use_stored", "products", "toplevel");
        $gateway->getDBQuery()->setUri($this->url('.'));

        return $products = $gateway->getAllProducts();
    }

    function getTranslation()
    {
        return $translation = $this->getKernel()->getTranslation('product');
    }

    function renderHtmlCreate()
    {
        $kernel = $this->context->getKernel();
        $kernel->module('product');
        $kernel->useModule('filemanager');

        $data = array();
        if (is_object($this->product_doctrine)) {
            $data['product'] = $this->product_doctrine;
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/edit');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $product = &$this->getProductDoctrine();

        $product->getDetails()->number = $_POST['number'];
        $product->getDetails()->Translation['da']->name = $_POST['name'];
        $product->getDetails()->Translation['da']->description = $_POST['description'];
        $product->getDetails()->price = new Ilib_Variable_Float($_POST['price'], 'da_dk');
        if(isset($_POST['before_price'])) $product->getDetails()->before_price = new Ilib_Variable_Float($_POST['before_price'], 'da_dk');
        if(isset($_POST['weight'])) $product->getDetails()->weight = new Ilib_Variable_Float($_POST['weight'], 'da_dk');
        if(isset($_POST['unit'])) $product->getDetails()->unit = $_POST['unit'];
        if(isset($_POST['vat'])) $product->getDetails()->vat = $_POST['vat'];
        if(isset($_POST['do_show'])) $product->do_show = $_POST['do_show'];
        if(isset($_POST['state_account_id'])) $product->getDetails()->state_account_id = (int)$_POST['state_account_id'];

        if(isset($_POST['has_variation'])) $product->has_variation = $_POST['has_variation'];
        if(isset($_POST['stock'])) $product->stock = $_POST['stock'];

        try {
            $product->save();

            if ($redirect->get('id') != 0) {
                $redirect->setParameter('product_id', $product->getId());
            }
            return new k_SeeOther($this->getPostRedirectUrl($product));
        } catch (Doctrine_Validator_Exception $e) {
            $this->product_doctrine = $product;
            $this->getError()->attachErrorStack($product->getCollectedErrorStack());
        }

        return $this->render();
    }

    function getPostRedirectUrl($product)
    {
        return $this->url($product->getId());
    }
}