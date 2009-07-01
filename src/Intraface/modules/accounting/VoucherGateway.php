<?php
/**
 * Voucher
 *
 * @package Intraface_Accounting
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */
class Intraface_modules_accounting_VoucherGateway
{
    public $year; // object

    /**
     * Constructor
     *
     * @param object  $year_object
     *
     * @return void
     */
    public function __construct($year_object)
    {
        $this->year = $year_object;
    }

    public function findFromId($id)
    {
        require_once dirname (__FILE__) . '/Voucher.php';
    	return new Voucher($this->year, $id);
    }

    /**
     * Creates a voucher
     *
     * @param object $year
     * @param string $voucher_number
     *
     * @return void
     */
    public function findFromVoucherNumber($voucher_number)
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM accounting_voucher WHERE number = '".safeToDb($voucher_number)."' AND year_id = '".$this->year->get('id')."' AND intranet_id = " . $this->year->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return new Voucher($this->year);
        }

        return new Voucher($this->year, $db->f('id'));
    }

    /**
     * @return (array)
     */
     function getList($filter = '')
     {
        $sql = "SELECT *, DATE_FORMAT(voucher.date, '%d-%m-%Y') AS date_dk
            FROM accounting_voucher voucher
            WHERE voucher.active = 1 AND voucher.year_id = ".$this->year->get('id')."
                AND voucher.intranet_id = ".$this->year->kernel->intranet->get('id');

        switch ($filter) {
            case 'lastfive':
                $sql .= " ORDER BY voucher.number DESC, voucher.id DESC LIMIT 5";
             break;
            default:
                $sql .= " ORDER BY voucher.number DESC, voucher.id DESC";
                break;
        }

         $db = new Db_Sql;

        $db->query($sql);

        if ($db->numRows() == 0) { return array(); }

        $list = array();
        $i = 0;
        while ($db->nextRecord()) {
            $list[$i]['id'] = $db->f('id');
            $list[$i]['number'] = $db->f('number');
            $list[$i]['text'] = $db->f('text');
            $list[$i]['date_dk'] = $db->f('date_dk');
            $i++;
        }
        return $list;
     }
}