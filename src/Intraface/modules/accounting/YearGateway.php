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

    function findFromId($id, $load_active = true)
    {
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
}