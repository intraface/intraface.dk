<?php
/**
 * Year
 *
 * @package Intraface_Accounting
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
require_once 'Intraface/modules/accounting/Account.php';

class Year extends Intraface_Standard
{
    public $id; // årsid
    public $kernel; // object
    public $value; // array
    public $error; // error object

    /**
     * Constructor
     *
     * @param $kernel
     * @param $year_id (integer)
     * @param $load_acttive (booelean) bruges fx n�r et nyt �r skal oprettes
     *
     * @return void
     */
    function __construct($kernel, $year_id = 0, $load_active = true)
    {
        $this->error  = new Intraface_Error;
        $this->kernel = $kernel;
        $this->id     = (int)$year_id;

        if ($this->id > 0) {
            $this->load();
        } elseif ($load_active) {
            if ($this->loadActiveyear() > 0) {
                $this->load();
            }
        }
    }

    /**
     * Funktion til at sætte et regnskabsår, som brugeren redigerer i.
     *
     * @return true
     */
    function setYear()
    {
        if ($this->id == 0) {
            return false;
        }
        $this->reset();

        $this->kernel->getSetting()->set('user', 'accounting.active_year', $this->id);

        return true;
    }

    /**
     * Finder det aktive år.
     *
     * @todo should be deprecated in favor of getActiveYear
     *
     * @return year / false
     */
    public function loadActiveYear()
    {
        $this->id = $this->kernel->getSetting()->get('user', 'accounting.active_year');
        return $this->id;
    }

    /**
     * Finds the ative year
     *
     * @return integer
     */
    function getActiveYear()
    {
        return $this->kernel->getSetting()->get('user', 'accounting.active_year');
    }

    /**
     * Checks whether a year isset.
     *
     * @param boolean $redirect Set to true if a redirect should occur if no year isset
     *
     * @return boolean
     */
    function checkYear($redirect = true)
    {
        // hvis ikke der er sat noget aktivt år, skal det sættes
        $active_year = $this->getActiveYear();
        if (!$this->_isValid()) {
            $active_year = 0;
        }

        if (!$active_year) {
            if ($redirect) {
                header('Location: years.php');
                exit;
            }
            return false;
        }
        return true;
    }

    function isYearSet()
    {
        // hvis ikke der er sat noget aktivt �r, skal det s�ttes
        $active_year = $this->loadActiveYear();
        if (!$this->_isValid()) {
            return false;
        }

        return true;
    }

    /**
     * Metode til at resette det aktive �r for den enkelte bruger.
     *
     * @return boolean
     */
    private function reset()
    {
        $this->kernel->getSetting()->set('user', 'accounting.active_year', 0);
        /*
        $db = new DB_Sql;
        $db->query("DELETE FROM accounting_year_active WHERE intranet_id = " . $this->kernel->intranet->get('id') . " AND user_id = " . $this->kernel->user->get('id'));
        */
        return true;
    }

    /*******************************************************************************
        OPDATERING OG LOAD
    *******************************************************************************/

    private function load()
    {
        if ($this->id == 0) {
            $this->value['id'] = 0;
            return;
        }

        $sql = "SELECT id,
                DATE_FORMAT(from_date, '%Y') AS year,
                DATE_FORMAT(from_date, '%d-%m-%Y') AS from_date_dk,
                DATE_FORMAT(to_date, '%d-%m-%Y') AS to_date_dk,
                last_year_id, from_date, to_date, locked, label, vat
            FROM accounting_year
            WHERE id = '" . $this->id . "'
                AND intranet_id = ".$this->kernel->intranet->get('id')."
            LIMIT 1";

        $db = new DB_Sql;
        $db->query($sql);

        if ($db->nextRecord()) {

            $this->id = $db->f('id');
            $this->value['id'] = $db->f('id');
            $this->value['year'] = $db->f('year');
            $this->value['label'] = $db->f('label');
            $this->value['last_year_id'] = $db->f('last_year_id');
            $this->value['from_date'] = $db->f('from_date');
            $this->value['from_date_dk'] = $db->f('from_date_dk');
            $this->value['to_date'] = $db->f('to_date');
            $this->value['to_date_dk'] = $db->f('to_date_dk');
            $this->value['locked'] = $db->f('locked');
            $this->value['vat'] = $db->f('vat');
        } else {
            $this->id = 0;
            $this->value['id'] = 0;
        }
    }

    private function validate(&$var)
    {
        $validator = new Intraface_Validator($this->error);
        // I could not find any use of the following, so i commented it out /SJ (22-01-2007)
        // $validator->isNumeric($var['year'], "year", "allow_empty");
        $validator->isNumeric($var['last_year_id'], "last_year_id", "allow_empty");
        $validator->isString($var['label'], "Du skal skrive et navn til året");
        $validator->isNumeric($var['locked'], "locked");
        settype($var['vat'], 'integer');
        $validator->isNumeric($var['vat'], "vat", 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Public: Metode til at opdatere �ret
     *
     * @param $var (array) Oplysninger om �ret
     */
    function save($var)
    {
        $var = safeToDb($var);

        $post_date_from = new Intraface_Date($var['from_date']);
        $post_date_from->convert2db();

        $post_date_to = new Intraface_Date($var['to_date']);
        $post_date_to->convert2db();

        if (!isset($var['last_year_id'])) {
            $var['last_year_id'] = 0;
        }

        if (!$this->validate($var)) {
            return false;
        }

        if ($this->id > 0) {
            $sql="UPDATE accounting_year ";
            $sql_after=" WHERE id='".$this->id."' AND intranet_id = ".$this->kernel->intranet->get('id')."";
        } else {
            $sql="INSERT INTO accounting_year ";
            $sql_after = ', date_created = NOW()';
        }
        $sql.=" SET
            intranet_id='".$this->kernel->intranet->get('id')."',
            user_id='".$this->kernel->user->get('id')."',
            last_year_id='".$var['last_year_id']."',
            label='".$var['label']."',
            from_date='".$post_date_from->get()."',
            to_date = '".$post_date_to->get()."',
            locked='".$var['locked']."',
            date_changed = NOW(),
            vat = '".(int)$var['vat']."'
            $sql_after";

        $db = new DB_Sql;
        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        $this->load();

        return $this->id;
    }

    /****************************************************************************
    VALIDERINGSFUNKTIONER
    ****************************************************************************/
    public function isValid()
    {
        return $this->_isValid();
    }

    /**
     * Metode til at tjekke om �ret findes
     *
     * @return 1 = year set; 0 = year NOT set
     */
    private function _isValid()
    {
        $sql = "SELECT id FROM accounting_year
            WHERE id = ".$this->id."
                AND intranet_id = ". $this->kernel->intranet->get('id') . " AND active = 1";

        $db = new DB_Sql;
        $db->query($sql);


        if (!$db->nextRecord()) {
            return false;
        }

        return true;
    }

    function vatAccountIsSet()
    {
        if ($this->get('vat') == 0) {
            return true; // vi lader som om de er sat, n�r der ikke er moms p� selve regnskabet
        }
        if ($this->getSetting('vat_in_account_id') > 0 AND $this->getSetting('vat_out_account_id') > 0 AND $this->getSetting('vat_balance_account_id') > 0) {
            return true;
        }
        return false;
    }

    /**
     * Funktion til at tjekke om �ret er l�st?
     *
     * @return boolean
     */
    public function isYearOpen()
    {
        $db = new Db_Sql;
        $db->query("SELECT locked FROM accounting_year WHERE id = " . $this->id . " AND intranet_id = ".$this->kernel->intranet->get('id'));
        if ($db->nextRecord()) {
            if ($db->f('locked') == 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Public: funktion til at tjekke om datoen er i aktuelle �r?
     *
     * @param (date) 0000-00-00
     *
     * @return boolean
     */
    public function isDateInYear($date)
    {
        if ($this->getId() == 0) {
        	throw new Exception('Year has not been loaded yet - maybe not saved');
        }

        $date = safeToDb($date);

        $db = new Db_Sql;
        $db->query("SELECT from_date, to_date FROM accounting_year WHERE id= " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id') . " LIMIT 1");
        if ($db->nextRecord()) {
          if ($db->f('from_date') <= $date AND $date <= $db->f('to_date')) {
              return true;
          }
        }
        return false;
    }

    /**
     * Checks whether the year is ready to use for stating
     *
     * @return boolean
     */
    public function readyForState($date = NULL)
    {
        if ($date === NULL) {
            $date = date('Y-m-d');
        }

        $return = true;

        if (!$this->get('id')) {
            $this->error->set('Der er ikke sat noget år.');
            $return = false;
        } elseif (!$this->isDateInYear($date)) {
            $this->error->set('Datoen er ikke i det år, der er sat i regnskabsmodulet.');
            $return = false;
        } elseif ($this->get('locked') == 1) {
            $this->error->set('Året er ikke åbent for bogføring.');
            $return = false;
        }

        return $return;
    }

    /**************************************************************************
        �VRIGE METODER
    **************************************************************************/


    /**
     * Gets a list
     *
     * @return array
     */
    function getList()
    {
        $gateway = new Intraface_modules_accounting_YearGateway($this->kernel);
        return $gateway->getList();
        /*
        if (!is_object($this->kernel)) {
            trigger_error('Du kan ikke k�re Year::getList() uden at have instatieret klassen', FATAL);
        }
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
        */
    }

    function getBalanceAccounts()
    {
        // afstemningskonti
        $balance_accounts = unserialize($this->getSetting('balance_accounts'));

        if (!is_array($balance_accounts)) {
        	throw new Exception('Balance accounts are not an array');
        }

        $sql_where = "";

        if (!empty($balance_accounts) AND count($balance_accounts) > 0) {
            foreach ($balance_accounts AS $account) {
                $sql_where .= "id = " . $account . " OR ";
            }
        }
        // hvis der ikke er nogen balance_accounts skal den ikke v�lge nogen poster
        $sql_where .= "id=0";

        $db = new DB_Sql;
        $db->query("SELECT id FROM accounting_account
            WHERE (".$sql_where.")
            	AND intranet_id = " . $this->kernel->intranet->get('id') . "
            	AND year_id = " . $this->get('id'));

        $accounts = array(); // afstemningskonti
        $i = 0;
        while ($db->nextRecord()) {
            $oAccount = new Account($this, $db->f('id'));
            $oAccount->getSaldo('stated');
            $saldo = $oAccount->get('saldo');
            $oAccount->getSaldo('draft'); // f�r et array

            $accounts[$i]['id'] = $oAccount->get('id');
            $accounts[$i]['name'] = $oAccount->get('name');
            $accounts[$i]['number'] = $oAccount->get('number');
            $accounts[$i]['saldo_primo'] = $saldo;
            $accounts[$i]['saldo_draft'] = (float)$oAccount->get('saldo_draft');
            $accounts[$i]['saldo_ultimo'] = $saldo + $oAccount->get('saldo_draft');
            $i++;
        }
        return $accounts;
    }

    function setSetting($setting, $value)
    {
        return $this->kernel->getSetting()->set('intranet', 'accounting.'.$setting, $value,  $this->get('id'));
    }

    function getSetting($setting)
    {
        return $this->kernel->getSetting()->get('intranet', 'accounting.' . $setting, $this->get('id'));
    }

    function createAccounts($type, $last_year_id = 0)
    {
        if ($this->getId() == 0) {
        	throw new Exception('Year has no id');
        }

        $last_year_id = (int)$last_year_id;
        switch ($type) {
            case 'standard':
                    $standardaccounts = array();
                    /*
                    // HACK
                    include(ROOT_PATH . 'intraface_modules/accounting/standardaccounts.php');
                    // HACK

                    if (empty($standardaccounts)) {
                        return 0;
                    }
                    */

                    // $module_accounting = $this->kernel->useModule('accounting');
                    // $module_accounting->includeFile('standardaccounts.php');
                    // HACK
                    // include($module_accounting->includeFile('standardaccounts.php')); // 'intraface_modules/accounting/standardaccounts.php');
                    // HACK

                    include('Intraface/modules/accounting/standardaccounts.php');

                    if (empty($standardaccounts)) {
                        return false;
                    }

                    $balance_accounts = array();
                    $buy_abroad = array();
                    $buy_eu = array();

                    foreach ($standardaccounts AS $input) {
                        if (!defined('INTRAFACE_K2')) {
                        // hack as long as everything is not utf8
                            $input = array_map('utf8_decode',$input);
                        }
                        require_once 'Intraface/modules/accounting/Account.php';
                        $account = new Account($this);
                        $input['vat_percent'] = $this->kernel->getSetting()->get('intranet', 'vatpercent');
                        $id = $account->save($input);

                        // settings
                        if (!empty($input['setting'])) {
                            $this->setSetting($input['setting'] . '_account_id', $id);
                        }
                        if (!empty($input['balance_account']) AND $input['balance_account'] == 1) {
                            $balance_accounts[] = $id;
                        }
                        if (!empty($input['result_account_id_start']) AND $input['result_account_id_start']) {
                            $this->setSetting('result_account_id_start', $id);
                        }

                        if (!empty($input['result_account_id_end']) AND $input['result_account_id_end']) {
                            $this->setSetting('result_account_id_end', $id);
                        }

                        if (!empty($input['balance_account_id_start']) AND $input['balance_account_id_start']) {
                            $this->setSetting('balance_account_id_start', $id);
                        }
                        if (!empty($input['capital_account']) AND $input['capital_account']) {
                            $this->setSetting('capital_account_id', $id);
                        }
                        if (!empty($input['balance_account_id_end']) AND $input['balance_account_id_end']) {
                            $this->setSetting('balance_account_id_end', $id);
                        }

                        if (!empty($input['buy_eu']) AND $input['buy_eu'] == 1) {
                            $buy_eu[] = $id;
                        }
                        if (!empty($input['buy_abroad']) AND $input['buy_abroad'] == 1) {
                            $buy_abroad[] = $id;
                        }

                    }

                    $this->setSetting('balance_accounts', serialize($balance_accounts));
                    $this->setSetting('buy_abroad_accounts', serialize($buy_abroad));
                    $this->setSetting('buy_eu_accounts', serialize($buy_eu));

                    // oprette indstillinger
                    // Hvilke indstillinger skal overf�res?

                  break;
            case 'transfer_from_last_year':
                    // oprette konti
                    if ($last_year_id == 0) {
                        return false;
                    }
                    $last_year = new Year($this->kernel, $last_year_id);
                    $account = new Account($last_year);
                    $accounts = $account->getList();

                    foreach ($accounts as $a) {
                        $old_account = new Account($last_year, $a['id']);
                        $input = $old_account->get();
                        $input['created_from_id'] = $old_account->get('id');
                        $new_account = new Account($this);
                        $new_account->save($input);
                    }
                    // overf�re indstillinger
                    // dette skal genneml�bes stille og roligt, da jeg skal tage de gamle kontiid
                    // og knytte dem an til den nye konto
                    if ($this->get('vat') > 0) {
                        $this->transferAccountSetting($last_year, 'vat_in_account_id');
                        $this->transferAccountSetting($last_year, 'vat_abroad_account_id');
                        $this->transferAccountSetting($last_year, 'vat_out_account_id');
                        $this->transferAccountSetting($last_year, 'vat_balance_account_id');
                        $this->transferAccountSetting($last_year, 'vat_free_account_id');
                        $this->transferAccountSetting($last_year, 'eu_sale_account_id');
                        //$this->transferAccountSetting($last_year, 'eu_buy_account_id');
                        //$this->transferAccountSetting($last_year, 'abroad_buy_account_id');
                    }
                    $this->transferAccountSetting($last_year, 'result_account_id');
                    $this->transferAccountSetting($last_year, 'debtor_account_id');
                    $this->transferAccountSetting($last_year, 'credit_account_id');

                    $this->transferAccountSetting($last_year, 'result_account_id_start');
                    $this->transferAccountSetting($last_year, 'result_account_id_end');
                    $this->transferAccountSetting($last_year, 'balance_account_id_start');
                    $this->transferAccountSetting($last_year, 'balance_account_id_end');
                    $this->transferAccountSetting($last_year, 'capital_account_id');

                    $balance_accounts = unserialize($last_year->getSetting('balance_accounts'));

                    $db = new DB_Sql;
                    $new_balance_accounts = array();

                    if (is_array($balance_accounts)) {
                        foreach ($balance_accounts as $key=>$id) {
                            $db->query("SELECT id FROM accounting_account WHERE year_id = ".$this->get('id')." AND intranet_id = ".$this->kernel->intranet->get('id')." AND created_from_id = " . (int)$id);
                            while ($db->nextRecord()) {
                                $new_balance_accounts[] = $db->f('id');
                            }
                        }
                    }
                    $this->setSetting('balance_accounts', serialize($new_balance_accounts));

                    $buy_eu_accounts = unserialize($last_year->getSetting('buy_eu_accounts'));

                    $db = new DB_Sql;
                    $new_buy_eu_accounts = array();

                    if (is_array($buy_eu_accounts)) {
                        foreach ($buy_eu_accounts AS $key=>$id) {
                            $db->query("SELECT id FROM accounting_account WHERE year_id = ".$this->get('id')." AND intranet_id = ".$this->kernel->intranet->get('id')." AND created_from_id = " . (int)$id);
                            while ($db->nextRecord()) {
                                $new_buy_eu_accounts[] = $db->f('id');
                            }
                        }
                    }
                    $this->setSetting('buy_eu_accounts', serialize($new_buy_eu_accounts));

                    $buy_abroad_accounts = unserialize($last_year->getSetting('buy_abroad_accounts'));

                    $db = new DB_Sql;
                    $new_buy_abroad_accounts = array();

                    if (is_array($buy_abroad_accounts)) {
                        foreach ($buy_abroad_accounts AS $key=>$id) {
                            $db->query("SELECT id FROM accounting_account WHERE year_id = ".$this->get('id')." AND intranet_id = ".$this->kernel->intranet->get('id')." AND created_from_id = " . (int)$id);
                            while ($db->nextRecord()) {
                                $new_buy_abroad_accounts[] = $db->f('id');
                            }
                        }
                    }
                    $this->setSetting('buy_abroad_accounts', serialize($new_buy_abroad_accounts));


                break;
            default:
                    trigger_error('Der skal v�lges en m�de at lave kontoplanen p�', FATAL);
                break;
        }
        return true;

    }

    function setSettings($input)
    {
        if ($this->get('vat') > 0) {
            if(empty($input['vat_in_account_id'])) $input['vat_in_account_id'] = 0;
            $this->setSetting('vat_in_account_id', (int)$input['vat_in_account_id']);

            if(empty($input['vat_out_account_id'])) $input['vat_out_account_id'] = 0;
            $this->setSetting('vat_out_account_id', (int)$input['vat_out_account_id']);

            if(empty($input['vat_abroad_account_id'])) $input['vat_abroad_account_id'] = 0;
            $this->setSetting('vat_abroad_account_id', (int)$input['vat_abroad_account_id']);

            if(empty($input['vat_balance_account_id'])) $input['vat_balance_account_id'] = 0;
            $this->setSetting('vat_balance_account_id', (int)$input['vat_balance_account_id']);

            if(empty($input['vat_free_account_id'])) $input['vat_free_account_id'] = 0;
            $this->setSetting('vat_free_account_id', (int)$input['vat_free_account_id']);

            if(empty($input['eu_sale_account_id'])) $input['eu_sale_account_id'] = 0;
            $this->setSetting('eu_sale_account_id', (int)$input['eu_sale_account_id']);
            //$this->setSetting('eu_buy_account_id', (int)$input['eu_buy_account_id']);
            //$this->setSetting('abroad_buy_account_id', (int)$input['abroad_buy_account_id']);
        }
        if(empty($input['result_account_id'])) $input['result_account_id'] = 0;
        $this->setSetting('result_account_id', (int)$input['result_account_id']);

        if(empty($input['debtor_account_id'])) $input['debtor_account_id'] = 0;
        $this->setSetting('debtor_account_id', (int)$input['debtor_account_id']);

        if(empty($input['credit_account_id'])) $input['credit_account_id'] = 0;
        $this->setSetting('credit_account_id', (int)$input['credit_account_id']);

        if(empty($input['balance_accounts'])) $input['balance_accounts'] = array();
        $this->setSetting('balance_accounts', serialize($input['balance_accounts']));

        if(empty($input['buy_abroad_accounts'])) $input['buy_abroad_accounts'] = array();
        $this->setSetting('buy_abroad_accounts', serialize($input['buy_abroad_accounts']));

        if(empty($input['buy_eu_accounts'])) $input['buy_eu_accounts'] = array();
        $this->setSetting('buy_eu_accounts', serialize($input['buy_eu_accounts']));


        if(empty($input['result_account_id_start'])) $input['result_account_id_start'] = 0;
        $this->setSetting('result_account_id_start', $input['result_account_id_start']);

        if(empty($input['result_account_id_end'])) $input['result_account_id_end'] = 0;
        $this->setSetting('result_account_id_end', $input['result_account_id_end']);

        if(empty($input['balance_account_id_start'])) $input['balance_account_id_start'] = 0;
        $this->setSetting('balance_account_id_start', $input['balance_account_id_start']);

        if(empty($input['balance_account_id_end'])) $input['balance_account_id_end'] = 0;
        $this->setSetting('balance_account_id_end', $input['balance_account_id_end']);

        if(empty($input['capital_account_id'])) $input['capital_account_id'] = 0;
        $this->setSetting('capital_account_id', $input['capital_account_id']);

        return true;
    }

    function getSettings()
    {
        if ($this->get('vat') > 0) {
            $setting['vat_in_account_id'] = $this->getSetting('vat_in_account_id');
            $setting['vat_out_account_id'] = $this->getSetting('vat_out_account_id');
            $setting['vat_abroad_account_id'] = $this->getSetting('vat_abroad_account_id');
            $setting['vat_balance_account_id'] = $this->getSetting('vat_balance_account_id');
            $setting['vat_free_account_id'] = $this->getSetting('vat_free_account_id');
            $setting['eu_sale_account_id'] = $this->getSetting('eu_sale_account_id');
            //$setting['eu_buy_account_id'] = $this->getSetting('eu_buy_account_id');
            //$setting['abroad_buy_account_id'] = $this->getSetting('abroad_buy_account_id');
        }
        $setting['result_account_id'] = $this->getSetting('result_account_id');
        $setting['debtor_account_id'] = $this->getSetting('debtor_account_id');
        $setting['credit_account_id'] = $this->getSetting('credit_account_id');
        $setting['balance_accounts'] = unserialize($this->getSetting('balance_accounts'));
        $setting['buy_eu_accounts'] = unserialize($this->getSetting('buy_eu_accounts'));
        $setting['buy_abroad_accounts'] = unserialize($this->getSetting('buy_abroad_accounts'));

        $setting['result_account_id_start'] = $this->getSetting('result_account_id_start');
        $setting['result_account_id_end'] = $this->getSetting('result_account_id_end');
        $setting['balance_account_id_start'] = $this->getSetting('balance_account_id_start');
        $setting['balance_account_id_end'] = $this->getSetting('balance_account_id_end');

        $setting['capital_account_id'] = $this->getSetting('capital_account_id');

        return $setting;
    }

    /**
     * @param $from->year (object)
     * @param $setting (string)
     */
    function transferAccountSetting($from_year, $setting)
    {
        $account_id = $from_year->getSetting($setting);
        $db = new DB_Sql;
        $db->query("SELECT id FROM accounting_account WHERE year_id = ".$this->get('id')." AND intranet_id = ".$this->kernel->intranet->get('id')." AND created_from_id = " . $account_id);
        if ($db->nextRecord()) {
            $this->setSetting($setting, $db->f('id'));
        }
    }

    function isSettingsSet()
    {
        if (!$this->getSetting('result_account_id_start') OR !$this->getSetting('result_account_id_end') OR !$this->getSetting('balance_account_id_start') OR !$this->getSetting('balance_account_id_end') OR !$this->getSetting('capital_account_id')) {
            return false;
        }
        return true;
    }

    /**
     *
     */
    function isBalanced()
    {
        $this->value['year_saldo'] = 0;

        $db = new DB_Sql;
        $db2 = new Db_Sql;

        $db->query("SELECT distinct(account.id)
            FROM accounting_account account
            LEFT JOIN accounting_post post
                ON account.id = post.account_id
            WHERE
                account.intranet_id = ".$this->kernel->intranet->get('id')."
                AND account.active = 1
                AND account.year_id = ".$this->get('id')."
            ORDER BY account.number ASC");
        $i = 0;
        while ($db->nextRecord()) {
            $account = new Account($this, $db->f('id'));

            # ikke sum konti
            if ($account->get('type_key') == array_search('sum', $account->types)) {
                continue;
            }

            $account->getSaldo();

            $i++;
        }

        // HACK midlertidigt hack round() indtil alle bel�b er integers i stedet for floats
        if (round($this->value['year_saldo']) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**************************************************************************
     * VALIDATE FUNCTIONS - IKKE EGENTLIG �RSRELATEREDE
     **************************************************************************/

    /**
     * Bruges i momsafregning og �rsafslutning
     *
     * M�rkeligt nok ser den ud til ike at returnere rigtigt!
     *
     * @return boolean
     */
    function isStated($type, $date_start, $date_end)
    {
        if (!$this->kernel->user->hasModuleAccess('debtor')) {
            return true;
        }

        require_once 'Intraface/modules/debtor/Debtor.php';
        $types = Debtor::getDebtorTypes();
        $type_key = array_search($type, $types);
        if (empty($type_key)) {
            throw new Exception('Ugyldig type');
        }
        $db = new DB_Sql;
        $sql = "SELECT id FROM debtor
            WHERE type= " . $type_key . "
                AND intranet_id = " .$this->kernel->intranet->get('id') . "
                AND (this_date BETWEEN '" . $date_start . "'
                AND '" .$date_end . "')
                AND voucher_id = 0";
        $db->query($sql);
        if ($db->numRows() == 0) {
            return true;
        }
        return false;
    }

    function lock()
    {
        $db = new DB_Sql;
        $db->query("UPDATE accounting_year SET locked = 1 WHERE id = " . $this->id);
        return true;
    }

    public function getId()
    {
    	return $this->get('id');
    }
}