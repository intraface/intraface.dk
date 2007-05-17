<?php
/**
 * Year
 *
 * @package Accounting
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
require_once 'Intraface/Standard.php';

class Year extends Standard {

    var $id; // årsid
    var $kernel; // object
    var $value; // array
    var $error; // error object

    /**
     * @param $kernel
     * @param $year_id (integer)
     * @param $load_acttive (booelean) bruges fx når et nyt år skal oprettes
     * @return void
     */

    function Year(& $kernel, $year_id = 0, $load_active = true) {
        if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
            trigger_error('Klassen Year kræver et Kernel-objekt', E_USER_ERROR);
            exit;
        }
        $this->error = new Error;
        $this->kernel = & $kernel;
        $this->id = (int)$year_id;

        if ($this->id > 0) {
            $this->load();
        }
        elseif ($load_active) {
            if ($this->loadActiveyear() > 0) {
                $this->load();
            }
        }
    }

    /**
     * Funktion til at sætte et regnskabsår, som brugeren redigerer i.
     *
     */
    function setYear() {
        if ($this->id == 0) {
            return 0;
        }

        $this->reset();

        $this->kernel->setting->set('user', 'accounting.active_year', $this->id);

        /*
        $db = new DB_sql;
        $db->query("INSERT INTO accounting_year_active SET year_id = ".$this->id.", intranet_id = " . $this->kernel->intranet->get('id') . ", user_id = " . $this->kernel->user->get('id'));
        */
        return 1;
    }

    /**
     * Finder det aktive år.
     *
     * @return year / false
     * @access public
     */
    function loadActiveYear() {
        return($this->id = $this->kernel->setting->get('user', 'accounting.active_year'));
        /*
        $db = new DB_Sql;

        $sql = "SELECT id FROM accounting_year
            WHERE
            intranet_id = " . $this->kernel->intranet->get('id') . "
                AND user_id = " . $this->kernel->user->get('id') . " AND id = '".$active_year_id."' LIMIT 1";

        $db->query($sql);

        if ($db->nextRecord()) {
            $this->load();
            return ($this->id = $db->f('id'));
        }

        return 0;
        */
    }

    /**
     * Funktion til at tjekke det enkelte år. Funktionen skal køres på alle siderne
     * under accounting
     */

    function checkYear($redirect = true) {
        // hvis ikke der er sat noget aktivt år, skal det sættes
        $active_year = $this->loadActiveYear();
        if (!$this->_isValid()) {
            $active_year = 0;
        }

        // if (!$active_year && basename($_SERVER['PHP_SELF']) != 'year.php') {
        if (!$active_year) {
            if ($redirect) {
                header('Location: years.php');
                exit;
            }
            return 0;
        }
        return 1;
    }

    function isYearSet() {
        // hvis ikke der er sat noget aktivt år, skal det sættes
        $active_year = $this->loadActiveYear();
        if (!$this->_isValid()) {
            return false;
        }

        return true;

    }

    /**
     * Metode til at resette det aktive år for den enkelte bruger.
     *
     * @access private
     */

    function reset() {
        $this->kernel->setting->set('user', 'accounting.active_year', 0);
        /*
        $db = new DB_Sql;
        $db->query("DELETE FROM accounting_year_active WHERE intranet_id = " . $this->kernel->intranet->get('id') . " AND user_id = " . $this->kernel->user->get('id'));
        */
    }

    /*******************************************************************************
        OPDATERING OG LOAD
    *******************************************************************************/

    function load() {

        if($this->id == 0) {
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
        }

    }

    function validate(&$var) {
        $validator = new Validator($this->error);
        // I could not find any use of the following, so i commented it out /SJ (22-01-2007)
        // $validator->isNumeric($var['year'], "year", "allow_empty");
        $validator->isNumeric($var['last_year_id'], "last_year_id", "allow_empty");
        $validator->isString($var['label'], "Du skal skrive et navn til året");
        $validator->isNumeric($var['locked'], "locked");
        settype($var['vat'], 'integer');
        $validator->isNumeric($var['vat'], "vat", 'allow_empty');

        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }


    /**
     * Public: Metode til at opdatere året
     *
     * @param $var (array) Oplysninger om året
     */

    function save($var) {
        $var = safeToDb($var);

        $post_date_from = new Intraface_Date($var['from_date']);
        $post_date_from->convert2db();

        $post_date_to = new Intraface_Date($var['to_date']);
        $post_date_to->convert2db();

        if (!$this->validate($var)) {
            return 0;
        }


        if($this->id > 0) {
            $sql="UPDATE accounting_year ";
            $sql_after=" WHERE id='".$this->id."' AND intranet_id = ".$this->kernel->intranet->get('id')."";
        }
        else {
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

    /**
     * Private: Metode til at tjekke om året findes
     *
     * @return 1 = year set; 0 = year NOT set
     * @access private
     */
    function _isValid() {
        $sql = "SELECT id FROM accounting_year
            WHERE id = ".$this->id."
                AND intranet_id = ". $this->kernel->intranet->get('id') . " AND active = 1";

        $db = new DB_Sql;
        $db->query($sql);


        if (!$db->nextRecord()) {
            return 0;
        }

        return 1;

    }

    function vatAccountIsSet() {
        if ($this->get('vat') == 0) {
            return 1; // vi lader som om de er sat, når der ikke er moms på selve regnskabet
        }
        if ($this->getSetting('vat_in_account_id') > 0 AND $this->getSetting('vat_out_account_id') > 0 AND $this->getSetting('vat_balance_account_id') > 0) {
            return 1;
        }
        return 0;
    }


    /**
     * Funktion til at tjekke om året er låst?
     *
     * @access public
     */
    function isYearOpen() {
        $db = new Db_Sql;
        $db->query("SELECT locked FROM accounting_year WHERE id = " . $this->id . " AND intranet_id = ".$this->kernel->intranet->get('id'));
        if ($db->nextRecord()) {
            if ($db->f('locked') == 1) {
                return 0;
            }
        }
        return 1;
    }

    /**
     * Public: funktion til at tjekke om datoen er i aktuelle år?
     *
     * @param (date) 0000-00-00
     * @access public
     */
    function isDateInYear($date) {
        $date = safeToDb($date);

        $db = new Db_Sql;
        $db->query("SELECT from_date, to_date FROM accounting_year WHERE id= " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id') . " LIMIT 1");
        if ($db->nextRecord()) {
          if ($db->f('from_date') <= $date AND $date <= $db->f('to_date')) {
              return 1;
          }
        }
        return 0;
    }



    /**************************************************************************
        ØVRIGE METODER
    **************************************************************************/


    /**
     *
     * @return (array)
     */

    function getList() {
        if (!is_object($this->kernel)) {
            trigger_error('Du kan ikke køre Year::getList() uden at have instatieret klassen', FATAL);
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
    }


    /**
     * TODO Skriv denne om til bare at hente fra get("last_year_id");
     *
     * @return (int) Sidste års regnskabsår regnskabår
     * @access public
     */
     /*
    function getLastYearId() {
        die("Bør bare hente fra get");
        $db = new Db_Sql;
        $db->query("SELECT last_year_id FROM accounting_year WHERE id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id') . " LIMIT 1");
        if ($db->nextRecord()) {
            return $db->f("last_year_id");
        }
        else {
            return 0;
        }
    }
    */

    function getBalanceAccounts() {
        // afstemningskonti
        $balance_accounts = unserialize($this->getSetting('balance_accounts'));

        $sql_where = "";

        if (!empty($balance_accounts) AND count($balance_accounts) > 0) {
            foreach($balance_accounts AS $account) {
                $sql_where .= "id = " . $account . " OR ";
            }
        }
        // hvis der ikke er nogen balance_accounts skal den ikke vælge nogen poster
        $sql_where .= "id=0";


        $db = new Db_sql;
        $db->query("SELECT id FROM accounting_account
            WHERE (".$sql_where.") AND intranet_id = " . $this->kernel->intranet->get('id') . " AND year_id = " . $this->get('id'));

        $accounts = array(); // afstemningskonti
        $i = 0;
        while ($db->nextRecord()) {
            $oAccount = new Account($this, $db->f('id'));
            $oAccount->getSaldo('stated');
            $saldo = $oAccount->get('saldo');
            $oAccount->getSaldo('draft'); // får et array

            $accounts[$i]['id'] = $oAccount->get('id');
            $accounts[$i]['name'] = $oAccount->get('name');
            $accounts[$i]['number'] = $oAccount->get('number');
            $accounts[$i]['saldo_primo'] = $saldo;
            $accounts[$i]['saldo_draft'] = $oAccount->get('saldo_draft');
            $accounts[$i]['saldo_ultimo'] = $saldo + $oAccount->get('saldo_draft');
            $i++;
        }
        return $accounts;
    }

    function setSetting($setting, $value) {
        return $this->kernel->setting->set('intranet', 'accounting.'.$setting, $value,  $this->get('id'));
    }

    function getSetting($setting) {
        return $this->kernel->setting->get('intranet', 'accounting.' . $setting, $this->get('id'));
    }

    function createAccounts($type, $last_year_id = 0) {
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
                    // print
                    // HACK
                    // include($module_accounting->includeFile('standardaccounts.php')); // 'intraface_modules/accounting/standardaccounts.php');
                    // HACK

                    require(PATH_ROOT . 'intraface/modules/accounting/standardaccounts.php');

                    if (empty($standardaccounts)) {
                        return 0;
                    }

                    $balance_accounts = array();
                    $buy_abroad = array();
                    $buy_eu = array();

                    foreach ($standardaccounts AS $input) {
                        $account = new Account($this);
                        $input['vat_percent'] = $this->kernel->setting->get('intranet', 'vatpercent');
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
                    // Hvilke indstillinger skal overføres?

                  break;
            case 'transfer_from_last_year':
                    // oprette konti
                    if ($last_year_id == 0) {
                        return 0;
                    }
                    $last_year = new Year($this->kernel, $last_year_id);
                    $account = new Account($last_year);
                    $accounts = $account->getList();

                    foreach ($accounts AS $a) {
                        $old_account = new Account($last_year, $a['id']);
                        $input = $old_account->get();
                        $input['created_from_id'] = $old_account->get('id');
                        $new_account = new Account($this);
                        $new_account->save($input);
                    }
                    // overføre indstillinger
                    // dette skal gennemløbes stille og roligt, da jeg skal tage de gamle kontiid
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
                        foreach ($balance_accounts AS $key=>$id) {
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
                    trigger_error('Der skal vælges en måde at lave kontoplanen på', FATAL);
                break;
        }
        return 1;

    }

    function setSettings($input) {
        if ($this->get('vat') > 0) {
            $this->setSetting('vat_in_account_id', (int)$input['vat_in_account_id']);
            $this->setSetting('vat_out_account_id', (int)$input['vat_out_account_id']);
            $this->setSetting('vat_abroad_account_id', (int)$input['vat_abroad_account_id']);
            $this->setSetting('vat_balance_account_id', (int)$input['vat_balance_account_id']);
            $this->setSetting('vat_free_account_id', (int)$input['vat_free_account_id']);
            $this->setSetting('eu_sale_account_id', (int)$input['eu_sale_account_id']);
            //$this->setSetting('eu_buy_account_id', (int)$input['eu_buy_account_id']);
            //$this->setSetting('abroad_buy_account_id', (int)$input['abroad_buy_account_id']);
        }
        $this->setSetting('result_account_id', (int)$input['result_account_id']);
        $this->setSetting('debtor_account_id', (int)$input['debtor_account_id']);
        $this->setSetting('credit_account_id', (int)$input['credit_account_id']);
        $this->setSetting('balance_accounts', serialize($input['balance_accounts']));
        $this->setSetting('buy_abroad_accounts', serialize($input['buy_abroad_accounts']));
        $this->setSetting('buy_eu_accounts', serialize($input['buy_eu_accounts']));

        $this->setSetting('result_account_id_start', $input['result_account_id_start']);
        $this->setSetting('result_account_id_end', $input['result_account_id_end']);
        $this->setSetting('balance_account_id_start', $input['balance_account_id_start']);
        $this->setSetting('balance_account_id_end', $input['balance_account_id_end']);

        $this->setSetting('capital_account_id', $input['capital_account_id']);

        return 1;
    }

    function getSettings() {
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
    function transferAccountSetting($from_year, $setting) {
        $account_id = $from_year->getSetting($setting);
        $db = new DB_Sql;
        $db->query("SELECT id FROM accounting_account WHERE year_id = ".$this->get('id')." AND intranet_id = ".$this->kernel->intranet->get('id')." AND created_from_id = " . $account_id);
        if ($db->nextRecord()) {
            $this->setSetting($setting, $db->f('id'));
        }
    }

    function isSettingsSet() {
        if (!$this->getSetting('result_account_id_start') OR !$this->getSetting('result_account_id_end') OR !$this->getSetting('balance_account_id_start') OR !$this->getSetting('balance_account_id_end') OR !$this->getSetting('capital_account_id')) {
            return 0;
        }
        return 1;
    }

    /**
     *
     */

    function isBalanced() {
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

        // HACK midlertidigt hack round() indtil alle beløb er integers i stedet for floats
        if (round($this->value['year_saldo']) == 0) {
            return true;
        }
        else {
            return false;
        }

    }

    /**************************************************************************
     * VALIDATE FUNCTIONS - IKKE EGENTLIG ÅRSRELATEREDE
     **************************************************************************/


    /**
     * Bruges i momsafregning og årsafslutning
     *
     * Mærkeligt nok ser den ud til ike at returnere rigtigt!
     *
     * @return boolean	Hvis nogen er fundet returneres 0 / Hvis ingen er fundet returneres 1
     *
     */
    function isStated($type, $date_start, $date_end) {
        if (!$this->kernel->user->hasModuleAccess('debtor')) {
            return 1;
        }

        $debtor_module = $this->kernel->useModule('debtor');
        $types = $debtor_module->getSetting('type');
        $type_key = array_search($type, $types);
        if (empty($type_key)) {
            trigger_error('Ugyldig type', E_USER_ERROR);
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
            return 1;
        }
        return 0;
    }

    function lock()  {
        $db = new DB_Sql;
        $db->query("UPDATE accounting_year SET locked = 1 WHERE id = " . $this->id);
        return 1;
    }
}

?>