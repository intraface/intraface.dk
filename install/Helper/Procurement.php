<?php
class Install_Helper_Procurement {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function create() {
        
        require_once 'Intraface/modules/procurement/Procurement.php';
        $procurement = new Procurement($this->kernel);
        return $procurement->update(array('dk_invoice_date' => '01-01-'.date('Y'), 'delivery_date' => '02-01-'.date('Y'), 'dk_payment_date' => '03-01-'.date('Y'), 'number' => 1, 'description' => 'test', 'dk_price_items' => '100,00', 'dk_price_shipment_etc' => '40,00', 'dk_vat' => '25,00'));
    }
}
?>
