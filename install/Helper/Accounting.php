<?php
class Install_Helper_Accounting {

    private $kernel;
    private $db;

    public function __construct($kernel, $db) {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    public function createYearWithVatAndStandardAccounts() {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->kernel);
        $year->save(array('from_date' => date('Y') . '-01-01', 'to_date' => date('Y') . '-12-31', 'label' => 'test', 'locked' => 0, 'vat' => 1));
        $year->createAccounts('standard');
        $year->setYear();


    }
}
?>
