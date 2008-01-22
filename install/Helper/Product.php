<?php
class Install_Helper_Product {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function create() {
        
        require_once 'Intraface/modules/product/Product.php';
        $product = new Product($this->kernel);
        
        return $product->save(array('name' => 'Product 1', 'price' => 100, 'unit' => 1));
    }
}
?>