<?php
class Install_Helper_Shop {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function create() {
        
        require_once 'Intraface/modules/shop/Shop.php';
        $shop = new Intraface_modules_shop_Shop;
        $shop->intranet_id = $this->kernel->intranet->getId();
        $shop->name = 'test';
        $shop->description = 'test';
        $shop->identifier = 'test';
        $shop->show_online = 1;
        $shop->payment_link = '/demo/1/shop/1/basket/onlinepayment';
        $shop->confirmation = '';
        $shop->receipt = '<h3>Vi har modtaget din ordre</h3><p>Nu sker der følgende:</p><p>Vi sender, du pakker du</p>';
        
        $shop->save();
        
        
        return 1;
    }
    
    public function createWithDefaultCurrency() {
        
        require_once 'Intraface/modules/shop/Shop.php';
        $shop = new Intraface_modules_shop_Shop;
        $shop->intranet_id = $this->kernel->intranet->getId();
        $shop->name = 'test';
        $shop->description = 'test';
        $shop->identifier = 'test';
        $shop->default_currency_id = 1;
        $shop->show_online = 1;
        $shop->confirmation = '';
        $shop->receipt = '<h3>Vi har modtaget din ordre</h3><p>Nu sker der følgende:</p><p>Vi sender, du pakker du</p>';
        
        $shop->save();
        
        
        return 1;
    }
    
    public function createCategory($name = 'Category 1', $identifier = 'category1', $parent = 0) {
        
        $category = new Intraface_Category($this->kernel, $this->db, new Intraface_Category_Type('shop', 1));
        $category->setIdentifier($identifier);
        $category->setName($name);
        $category->setParentId($parent);
        $category->save();
        
        return $category;
        
    }
}
?>