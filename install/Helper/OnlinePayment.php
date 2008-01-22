<?php
class Install_Helper_OnlinePayment {
    
    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }
    
    public function createAndAttachToOrder() {
        
        require_once 'Intraface/modules/onlinepayment/OnlinePayment.php';
        $onlinepayment = new OnlinePayment($this->kernel);
        if(!$onlinepayment->save(array('belong_to' => 'order', 'belong_to_id' => 1, 'transaction_number' => 111, 'transaction_status' => '000', 'amount' => 200))) {
            echo $onlinepayment->error->view();
            die;
        }
    }
    
}
?>
