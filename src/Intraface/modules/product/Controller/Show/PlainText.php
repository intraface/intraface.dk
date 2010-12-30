<?php
class Intraface_modules_product_Controller_Show_PlainText extends k_Component
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
        $filehandler = new FileHandler($this->getKernel());
        $data = array(
            'gateway' => $this->getGateway(),
            'product' => $this->getProduct(),
            'kernel' => $this->getKernel(),
            'filehandler' => $filehandler,
            'db' => $this->mdb2
        );
        
        $smarty = $this->template->create(dirname(__FILE__) . '/../tpl/show-plain-text');
        return $smarty->render($this, $data);
    }

    function getProduct()
    {
        return $this->context->getProduct();
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
        return $this->getProductDoctrine();
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
        if (!is_object($this->error)) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getTranslation());
        }

        return $this->error;
    }

    function getGateway()
    {
        return new Intraface_modules_product_ProductDoctrineGateway($this->doctrine_connection, $this->getKernel()->user);
    }
}