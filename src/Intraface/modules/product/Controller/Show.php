<?php
class Appender extends Ilib_Filehandler_AppendFile
{
	function __construct($kernel, $belong_to, $belong_to_id)
    {
    	$this->registerBelongTo(4, 'product');
        parent::__construct($kernel, $belong_to, $belong_to_id);
    }
}

class Intraface_modules_product_Controller_Show extends k_Component
{
    function map($name)
    {
        if ($name == 'filehandler') {
            return 'Intraface_Filehandler_Controller_Index';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        } elseif ($name == 'related') {
            return 'Intraface_modules_product_Controller_Related';
        } elseif ($name == 'stock') {
            // @todo check whether product is stock product
            return 'Intraface_modules_stock_Controller_Product';
        } elseif ($name == 'variations') {
            return 'Intraface_modules_product_Controller_Show_Variations';
        } elseif ($name == 'selectvariation') {
            return 'Intraface_modules_product_Controller_Selectproductvariation';
        }
    }

    function getObject()
    {
    	$kernel = $this->context->getKernel();

        /* $gateway = $this->getGateway();
        // when doctrine is implemented
        return $gateway->findById($this->name()); */
    	
    	require_once 'Intraface/modules/product/Product.php';
    	return new Product($kernel, $this->name());
    	
    }

    function getGateway()
    {
    	$kernel = $this->context->getKernel();
    	Intraface_Doctrine_Intranet::singleton($kernel->intranet->getId());

    	return new Intraface_modules_product_ProductDoctrineGateway(Doctrine_Manager::connection(), $kernel->user);
    }

    function getProduct()
    {
        return $this->getObject();
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $kernel = $this->getKernel();
        if (!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
            return new k_SeeOther($this->url('filehandler/selectfile'));
        }

        $product = $this->getGateway()->findById((int)$this->name());

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
print_r($product->asArray(true)); die('AA');
        try {
            $product->save();

            if ($redirect->get('id') != 0) {
                $redirect->setParameter('product_id', $product->getId());
            }
            return new k_SeeOther($this->url());
        } catch (Doctrine_Validator_Exception $e) {
            $error = new Intraface_Doctrine_ErrorRender($translation);
            $error->attachErrorStack($product->getErrorStack());
            $error->attachErrorStack($product->getDetails()->getErrorStack());
        }
    }

    function renderHtml()
    {
        $kernel = $this->context->getKernel();
        $kernel->module('product');
        $kernel->useShared('filehandler');
        $translation = $kernel->getTranslation('product');
        $product = new Product($kernel, $this->name());
        $filehandler = new FileHandler($kernel);
        $data = array(
            'gateway' => $this->getGateway(), 'product' => $product, 'translation' => $translation, 'kernel' => $kernel, 'filehandler' => $filehandler
        );

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/show.tpl.php');
        return $smarty->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $kernel = $this->context->getKernel();
        $kernel->module('product');
        $kernel->useShared('filehandler');
        $translation = $kernel->getTranslation('product');
        $product = new Product($kernel, $this->name());
        $filehandler = new FileHandler($kernel);

        $data = array(
            'gateway' => $this->getGateway(), 'product' => $this->getProduct(), 'translation' => $translation, 'kernel' => $kernel, 'filehandler' => $filehandler
        );

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/edit.tpl.php');
        return $smarty->render($this);
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getFileAppender()
    {
    	$kernel = $this->context->getKernel();
        $kernel->module('product');
        $product = new Product($kernel, $this->name);
        return new Appender($kernel, 'product', $product->get('id'));
    }

    function renderHtmlDelete()
    {
        $form = new HTML_QuickForm(null, 'post', $this->url(null, array('delete')));
        $form->addElement('hidden', '_method', 'delete');
        $form->addElement('submit', null, $this->t('Delete'));
        return $form->toHtml();
    }

    function DELETE()
    {
        $kernel = $this->getKernel();
        $kernel->module('product');
        $product = new Product($this->getKernel(), $this->name());
        if ($id = $product->delete()) {
            return new k_SeeOther($this->url('../'));
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtmlCopy()
    {
        $kernel = $this->getKernel();
        $kernel->module('product');
        $product = new Product($this->getKernel(), $this->name());
        if ($id = $product->copy()) {
            return new k_SeeOther($this->url('../' . $id));
        }
    }
}