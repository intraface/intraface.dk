<?php
/**
 * @package Intraface_Accounting
 */
require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'DB/Sql.php';

class VatPeriod extends Standard
{
    public $error;
    public $year;
    protected $id;
    public $value;
    public $status = array(
        0 => 'created',
        1 => 'saved',
        2 => 'stated'
    );

    public function __construct($year_object, $id = 0)
    {
        if (!is_object($year_object)) {
            trigger_error('Vat::Vat skal have Year', E_USER_ERROR);
        }

        $this->year  = $year_object;
        $this->id    = (int) $id;
        $this->error = new Error;
        if ($this->id > 0) {
            $this->load();
        }
    }

    private function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT *, DATE_FORMAT(date_start, '%d-%m-%Y') AS date_start_dk, DATE_FORMAT(date_end, '%d-%m-%Y') AS date_end_dk FROM accounting_vat_period WHERE id = " . $this->id . " AND intranet_id = " . $this->year->kernel->intranet->get('id'));

        if (!$db->nextRecord()) {
            return false;
        }

        $this->value['id']             = $db->f('id');
        $this->value['date_start']     = $db->f('date_start');
        $this->value['date_start_dk']  = $db->f('date_start_dk');
        $this->value['date_end']       = $db->f('date_end');
        $this->value['date_end_dk']    = $db->f('date_end_dk');
        $this->value['status_key']     = $db->f('status');
        $this->value['label']          = $db->f('label');
        $this->value['status']         = $this->status[$db->f('status')];
        $this->value['voucher_id']     = $db->f('voucher_id');
        $voucher                       = new Voucher($this->year, $this->value['voucher_id']);
        $this->value['voucher_number'] = $voucher->get('number');

        return true;
    }

    public function isStated()
    {
        $db = new DB_Sql;
        $db->query("SELECT status FROM accounting_vat_period WHERE status = 2 AND id = " . $this->id . " AND intranet_id=" . $this->year->kernel->intranet->get('id'). " AND active = 1");
        if ($db->nextRecord()) {
            return $db->numRows();
        }
        return false;
    }

    /**
     * Hente momsopgivelser fra i år
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
    public function periodsCreated()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM accounting_vat_period WHERE year_id = " . $this->year->get('id') . " AND intranet_id=" . $this->year->kernel->intranet->get('id'). " AND active=1");
        return $db->numRows();
    }

    /**
     * @return boolean
     */
    public function createPeriods()
    {
        if ($this->periodsCreated()) {
            // we will just pretend everything went fine
            return true;
        }

        $db = new DB_Sql;

        // momsperiode
        $module  = $this->year->kernel->getPrimaryModule();
        $periods = $module->getSetting('vat_periods');
        $periods = $periods[$this->year->getSetting('vat_period')];
        foreach ($periods['periods'] AS $key=>$value) {
            $input = array(
                'label'      =>  $value['name'],
                'date_start' =>  $this->year->get('year') . '-' . $value['date_from'],
                'date_end'   => $this->year->get('year') . '-' . $value['date_to'],
            );
            $this->save($input, 'insert');
        }

        return true;
    }

    /**
     * @return boolean
     */
    private function validate($input)
    {
        // bør også validere type
        return true;
    }

    /**
     * Saves
     *
     * @param array $input
     * @param string $type type mulige (insert) bruges af create så den ikke bare opdaterer tidligere
     *
     * @return integer
     */
    public function save($input, $type='')
    {
        $input = safeToDB($input);

        if (!$this->validate($input)) {
            return 0;
        }
        $db = new DB_Sql;
        if ($this->id == 0 OR $type=='insert') {
            $sql_type = "INSERT INTO ";
            $sql_end  = ", date_created = NOW()";
        } else {
            $sql_type = "UPDATE ";
            $sql_end  = " WHERE id = " . $this->id;
        }

        $sql = $sql_type . "accounting_vat_period SET user_id = ".$this->year->kernel->user->get('id').", label = '".$input['label']."', date_start = '".$input['date_start']."', date_end='".$input['date_end']."', date_updated=NOW(), intranet_id=".$this->year->kernel->intranet->get('id').", year_id=".$this->year->get('id')."" . $sql_end;

        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
        return $this->id;
    }

    /**
     * Set vat period to stated
     *
     * @param integer $voucher_id
     *
     * @return boolean
     */
    protected function setStated($voucher_id)
    {
        $db = new DB_Sql;
        $db->query("UPDATE accounting_vat_period SET voucher_id = '".(int)$voucher_id."', status = 2 WHERE id = ".$this->id." AND intranet_id = " . $this->year->kernel->intranet->get('id') . " AND year_id=". $this->year->get('id'));
        return true;
    }

    /**
     * @return boolean
     */
    function delete()
    {
        $db = new DB_Sql;
        if ($this->isStated()) {
            $this->error->set('Du kan ikke slette en periode, der er bogført');
            return false;
        }
        $db->query("UPDATE accounting_vat_period SET active = 0, date_updated = NOW() WHERE id = " . $this->id . " AND intranet_id = " . $this->year->kernel->intranet->get('id'));
        return true;
    }

    protected function getAccount($id)
    {
        return new Account($this->year, (int)$id);
    }

    /**
     * @return boolean
     */
    protected function loadAmounts()
    {
        $saldo_total    = 0; // integer med total saldo
        $saldo_rubrik_a = 0;
        $date_from      = $this->get('date_start');
        $date_to        = $this->get('date_end');

        // Salgsmoms - udgående
        $account_vat_in = $this->getAccount($this->year->getSetting('vat_out_account_id'));
        $account_vat_in->getSaldo('stated', $date_from, $date_to);
        $this->value['account_vat_out'] = $account_vat_in;

        // ganges med -1 for at få rigtigt fortegn til udregning
        $this->value['saldo_vat_out'] = $account_vat_in->get('saldo');
        $saldo_total += -1 * $this->value['saldo_vat_out']; // total

        // Moms af varekøb i udlandet
        // Dette beløb er et udregnet beløb, som udregnes under bogføringen
        $account_vat_abroad = $this->getAccount($this->year->getSetting('vat_abroad_account_id'));
        $account_vat_abroad->getSaldo('stated', $date_from, $date_to);
        $this->value['account_vat_abroad'] = $account_vat_abroad;

        // ganges med -1 for at få rigtigt fortegn til udregning
        $this->value['saldo_vat_abroad'] = $account_vat_abroad->get('saldo');
        $saldo_total += -1 * $this->value['saldo_vat_abroad'];

        // Købsmoms
        // Købsmomsen inkluderer også den udregnede moms af moms af varekøb i udlandet.
        // Dette beløb er lagt på kontoen under bogføringen.
        $account_vat_out = $this->getAccount($this->year->getSetting('vat_in_account_id'));
        $account_vat_out->getSaldo('stated', $date_from, $date_to);
        $this->value['account_vat_in'] = $account_vat_out;

        $this->value['saldo_vat_in'] = $account_vat_out->get('saldo');
        $saldo_total -= $this->value['saldo_vat_in'];

        // Rubrik A
        // EU-erhvervelser - her samles forskellige konti og beløbet udregnes.
        // Primosaldoen skal ikke medregnesa
        $buy_eu_accounts = unserialize($this->year->getSetting('buy_eu_accounts'));
        $this->value['saldo_rubrik_a'] = 0;
        $saldo_rubrik_a = 0;

        if (is_array($buy_eu_accounts) AND count($buy_eu_accounts) > 0) {
            foreach ($buy_eu_accounts AS $key=>$id) {
                $account_eu_buy = new Account($this->year, $id);
                $primo = $account_eu_buy->getPrimoSaldo();
                $account_eu_buy->getSaldo('stated', $date_from, $date_to);
                $saldo_rubrik_a += $account_eu_buy->get('saldo');
                $saldo_rubrik_a -= $primo['saldo'];

            }
        }
        $this->value['saldo_rubrik_a'] = $saldo_rubrik_a;

        // Rubrik B
        // Værdien af varesalg uden moms til andre EU-lande (EU-leverancer). Udfyldes
        // denne rubrik, skal der indsendes en liste med varesalgene uden moms.

        // Vi understøtter ikke rubrikken

        // Rubrik C
        // Værdien af varesalg uden moms til andre EU-lande (EU-leverancer). Udfyldes
        // denne rubrik, skal der indsendes en liste med varesalgene uden moms.

        // Vi understøtter ikke rubrikken

        $this->value['saldo_total'] = $saldo_total;

        return true;
    }

    /**
     * States period
     *
     * @param string $date
     * @param string $voucher_number
     *
     * @return boolean
     */
    function state($date, $voucher_number)
    {
        $skip_daybook = true; // bør kun være false i testsituationer

        if ($this->getId() == 0) {
            return false;
        }
        $this->loadAmounts();

        // kontoen er loadet af loadAmounts();
        $account_vat_balance   = $this->getAccount($this->year->getSetting('vat_balance_account_id'));
        $account_vat_in        = $this->get('account_vat_in');
        $account_vat_out       = $this->get('account_vat_out');
        $account_vat_abroad    = $this->get('account_vat_abroad');
        $saldo_rubrik_a        = $this->get('saldo_rubrik_a');
        $saldo_total           = $this->get('saldo_total');
        $var['date']           = $date;
        $var['voucher_number'] = $voucher_number;

        // Bogføring af udgående moms (salg)
        $var['text'] = 'Momsafregning - udgående moms, salg';
        // hvis beløbet er mindre end nul, skal der byttes om på konti
        // tjek lige om det samme skal laves andre steder
        if ($this->get('saldo_vat_out') > 0) {
            $var['debet_account_number']  = $account_vat_balance->get('number');
            $var['credit_account_number'] = $account_vat_out->get('number');
        } else {
            $var['debet_account_number']  = $account_vat_out->get('number');
            $var['credit_account_number'] = $account_vat_balance->get('number');
        }
        $var['amount'] = abs(round($this->get('saldo_vat_out')));

        $voucher = $this->getVoucher($var['voucher_number']);
        if (!$voucher->saveInDaybook($var, $skip_daybook)) {
            $this->error->set('Systemet kunne ikke opdatere udgående moms');
        }
        // Moms af varekøb i udlandet
        $var['text'] = 'Momsafregning - moms af køb i udlandet';
        $var['amount'] = $this->get('saldo_vat_abroad');

        if ($this->get('saldo_vat_abroad') < 0) {
            $var['debet_account_number'] = $account_vat_balance->get('number');
            $var['credit_account_number'] = $account_vat_abroad->get('number');
        } else {
            $var['debet_account_number'] = $account_vat_abroad->get('number');
            $var['credit_account_number'] = $account_vat_balance->get('number');
        }

        if (!$voucher->saveInDaybook($var, $skip_daybook)) {
            $this->error->set('Systemet kunne ikke opdatere moms moms af varekøb i andre lande');
        }

        // Indgående moms (køb)
        $var['text'] = 'Momsafregning - indgående moms, køb';
        if ($this->get('saldo_vat_in') > 0) {
            $var['debet_account_number'] = $account_vat_balance->get('number');
            $var['credit_account_number'] = $account_vat_in->get('number');

        } else {
            $var['debet_account_number'] = $account_vat_in->get('number');
            $var['credit_account_number'] = $account_vat_balance->get('number');
        }
        $var['amount'] = abs(round($this->get('saldo_vat_in')));

        if (!$voucher->saveInDaybook($var, $skip_daybook)) {
             $this->error->set('Systemet kunne ikke opdatere indgående moms');
        }

        if ($this->error->isError()){
            return false;
        }

        $this->setStated($voucher->getId());

        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array(
            'description' => 'Momsafregning ' . $this->get('date_start_dk') . ' til ' . $this->get('date_end_dk'),
            'belong_to' => 'vat',
            'belong_to_id' => $this->getId()))) {
            $this->error->merge($voucher_file->error->getMessage());
            $this->error->set('Filen blev ikke overflyttet');
        }

        return true;
    }

    protected function getVoucher($id)
    {
        return Voucher::factory($this->year, $id);
    }

    /**
     * @deprecated should be removed from everywhere
     */
    function compareAmounts()
    {
        return $this->isBalanced();
    }

    public function isBalanced()
    {
        if (!$this->isStated()) {
            // beløbene er ikke bogført endnu, så vi lader som om at det hele passer
            return true;
        }
        $this->loadAmounts();
        $amount_from_accounting = $this->value['saldo_total']; // fra regnskabet

        if ($amount_from_accounting != 0) {
            return false;
        }
        return true;
    }

    public function getId()
    {
        return $this->id;
    }
}