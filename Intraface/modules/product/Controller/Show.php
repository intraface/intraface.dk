<?php
class Appender extends Ilib_Filehandler_AppendFile
{
	function __construct($kernel, $belong_to, $belong_to_id)
    {
    	$this->registerBelongTo(4, 'product');
        parent::__construct($kernel, $belong_to, $belong_to_id);
    }
}

class Intraface_modules_product_Controller_Show extends k_Controller
{
    public $map = array('filehandler' => 'Intraface_Filehandler_Controller_Index',
                        'keyword' => 'Intraface_Keyword_Controller_Index');

    function getObject()
    {
    	$kernel = $this->registry->get('intraface:kernel');
        $gateway = new Intraface_modules_product_Gateway($kernel);
        return $gateway->getFromId($this->name);
    }

    function POST()
    {
        $kernel = $this->registry->get('intraface:kernel');
        if (!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
            throw new k_http_Redirect($this->url('filehandler/selectfile'));
        }
    }

    function GET()
    {
        $kernel = $this->registry->get('intraface:kernel');
        $kernel->module('product');
        $kernel->useShared('filehandler');
        $translation = $kernel->getTranslation('product');
        $product = new Product($kernel, $this->name);
        $filehandler = new FileHandler($kernel);
        $data = array(
            'product' => $product, 'translation' => $translation, 'kernel' => $kernel, 'filehandler' => $filehandler
        );

        return $this->render(dirname(__FILE__) . '/tpl/show.tpl.php', $data);
    }

    function getFileAppender()
    {
    	$kernel = $this->registry->get('intraface:kernel');
        $kernel->module('product');
        $product = new Product($kernel, $this->name);
        return new Appender($kernel, 'product', $product->get('id'));
    }
}