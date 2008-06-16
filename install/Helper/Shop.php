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
        $shop->confirmation = '';
        $shop->receipt = '<h3>Vi har modtaget din ordre</h3><p>Nu sker der følgende:</p><p>Vi sender, du pakker du</p>';
        
        $shop->save();
        
        
        return 1;
    }
}
?>