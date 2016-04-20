<?php
/**
 * Year
 *
 * @package Intraface_Accounting
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */
class Intraface_modules_accounting_YearGateway
{
    /**
     * @var object
     */
    public $kernel;

    /**
     * Constructor
     *
     * @param $kernel
     *
     * @return void
     */
    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function findById($id, $load_active = true)
    {
        require_once dirname(__FILE__) . '/Year.php';
        return new Year($this->kernel, $id, $load_active);
    }

    /**
     * Gets a list
     *
     * @return array
     */
    function getList()
    {
        $sql = "SELECT id, label FROM accounting_year
            WHERE intranet_id = ".$this->kernel->intranet->get('id')."
            ORDER BY from_date ASC";

        $db = new DB_Sql;
        $db->query($sql);

        if ($db->numRows() == 0) {
            return array();
        }

        while ($db->nextRecord()) {
            $account_years[$db->f("id")]['id'] = $db->f("id");
            $account_years[$db->f("id")]['label'] = $db->f("label");
        }

        return $account_years;
    }

    function findByVoucherId($voucher_id)
    {
        $sql = "SELECT id FROM accounting_voucher
            WHERE intranet_id = ".$this->kernel->intranet->get('id')."
            AND id = " . (int)$voucher_id . "
            LIMIT 1";

        $db = new DB_Sql;
        $db->query($sql);

        if ($db->numRows() == 0) {
            return false;
        }

        return $this->findById($db->f('year_id'));
    }
}
