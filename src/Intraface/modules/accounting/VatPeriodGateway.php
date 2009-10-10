<?php
/**
 * @package Intraface_Accounting
 */
class Intraface_modules_accounting_VatPeriodGateway
{
    public $year;

    public function __construct($year_object)
    {
        $this->year  = $year_object;
    }

    /**
     * Hente momsopgivelser fra i ï¿½r
     *
     * @return array
     */
    public function getList()
    {
        $db = new DB_Sql;
        $db->query("SELECT *, DATE_FORMAT(date_start, '%d-%m-%Y') AS date_start_dk, DATE_FORMAT(date_end, '%d-%m-%Y') AS date_end_dk FROM accounting_vat_period WHERE year_id = " . $this->year->get('id') . " AND intranet_id=" . $this->year->kernel->intranet->get('id') . " AND active = 1 ORDER BY date_start ASC");
        $i   = 0;
        $vat = array();
        while ($db->nextRecord()) {
            $vat[$i]['id']            = $db->f('id');
            $vat[$i]['label']         = $db->f('label');
            $vat[$i]['date_start']    = $db->f('date_start');
            $vat[$i]['date_end']      = $db->f('date_end');
            $vat[$i]['date_start_dk'] = $db->f('date_start_dk');
            $vat[$i]['date_end_dk']   = $db->f('date_end_dk');
            $vat[$i]['voucher_id']    = $db->f('voucher_id');
            $i++;
        }
        return $vat;
    }

    /**
     * @return integer
     */
    public function arePeriodsCreated()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM accounting_vat_period WHERE year_id = " . $this->year->get('id') . " AND intranet_id=" . $this->year->kernel->intranet->get('id'). " AND active=1");
        return $db->numRows();
    }

    public static function getPeriodsArray()
    {
        return array(
                // halvï¿½rlig
                0 => array(
                    'name' => 'Halvårlig',
                    'periods' => array(
                        // 1. halvï¿½r
                        1 => array(
                            'name' => '1. halvår',
                            'date_from' => '01-01',
                            'date_to' => '06-30'
                        ),
                        // 2. halvï¿½r
                        2 => array(
                            'name' => '2. halvår',
                            'date_from' => '07-01',
                            'date_to' => '12-31'
                        )
                    )
                ),
                // kvartalsvis
                1 => array(
                    'name' => 'Kvartalsvis',
                    'periods' => array(
                        // januarkvartal
                        1 => array(
                            'name' => '1. kvartal',
                            'date_from' => '01-01',
                            'date_to' => '03-31'
                        ),
                        // februarkvartal
                        2 => array(
                            'name' => '2. kvartal',
                            'date_from' => '04-01',
                            'date_to' => '06-30'
                        ),
                        // februarkvartal
                        3 => array(
                            'name' => '3. kvartal',
                            'date_from' => '07-01',
                            'date_to' => '09-30'
                        ),
                        // februarkvartal
                        4 => array(
                            'name' => '4. kvartal',
                            'date_from' => '10-01',
                            'date_to' => '12-31'
                        )
                    )
                )
            );
    }

    /**
     * @return boolean
     */
    public function createPeriods()
    {
        if ($this->arePeriodsCreated()) {
            // we will just pretend everything went fine
            return true;
        }

        $db = new DB_Sql;

        // momsperiode
        //$module  = $this->year->kernel->getPrimaryModule();
        //$periods = $module->getSetting('vat_periods');
        $periods = self::getPeriodsArray();
        $periods = $periods[$this->year->getSetting('vat_period')];
        foreach ($periods['periods'] as $key=>$value) {
            $input = array(
                'label'      => $value['name'],
                'date_start' => $this->year->get('year') . '-' . $value['date_from'],
                'date_end'   => $this->year->get('year') . '-' . $value['date_to'],
            );
            $vatperiod = $this->findFromId();
            $vatperiod->save($input, 'insert');
        }

        return true;
    }

    function findFromId($id = 0)
    {
    	require_once dirname(__FILE__) . '/VatPeriod.php';
        return new VatPeriod($this->year);
    }
}