<?php
/**
 * Account
 *
 * @package Accounting
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/tools/Amount.php';
require_once 'Intraface/3Party/Database/Db_sql.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/Validator.php';

class Account extends Standard {

    var $id; // kontoid
    var $year; // object
    var $value; // holds values for account if loaded
    var $error; // errorobject

    var $vat_percent;

    var $vat = array(
        0 => 'none',
        1 => 'in',
        2 => 'out'
    );

    // Disse bør laves om til engelske termer med småt og så oversættes
    // husk at ændre tilsvarende i validForState() - Status bør
    // splittes op i to konti (aktiver og passiver)
    // husk at opdatere databasen til alle sum-konti skal have nummer 5 i stedet

    var $types = array(
        1 => 'headline',
        2 => 'operating', // drift
        3 => 'balance, asset', // aktiv
        4 => 'balance, liability', // passiv
        5 => 'sum'
    );

    var $use = array(
        1 => 'none',
        2 => 'income',
        3 => 'expenses',
        4 => 'finance'
    );

    /**
     * Init:
     *
     * Account objektet repræsenterer _en_ konto.
     * Man kan finde kontonummeret ud fra kontonummeret også.
     *
     * Der laves en særlig metode til at hente saldoen, for den kræver en
     * del udregningskraft. Værdierne smides imidlertid også bare i values,
     * så man kan hente dem med $this->get()
     *
     * @param $intranet (object)
     * @param $user (object)
     * @param $account_id (int) valgfri konto id
     * @return void
     * @access public
     */
    function Account($year, $account_id = 0) {
        if (empty($year) OR !is_object($year)) {
            trigger_error('Account::Account kræver objektet Year.', E_USER_ERROR);
            exit;
        }

        $this->error = new Error;
        $this->year = $year;
        $this->id = (int)$account_id;

        $this->vatpercent = $this->year->kernel->setting->get('intranet', 'vatpercent');

        if ($this->id > 0) {
            $this->load();
        }

    }


    /**
     * Denne funktion bruges bl.a. under bogføringen, så man bare taster kontonummer
     * og så sættes den rigtige konto.
     *
     * @param (integer) $account_number
     * @access public
     */

    function factory($year, $account_number) {
        $account_number = (int)$account_number;

        if ($year->get('id') == 0) {
            return 0;
        }

        $sql = "SELECT id FROM accounting_account
            WHERE number = '".$account_number."'
                AND intranet_id = ".$year->kernel->intranet->get('id')."
                AND year_id = ".$year->get('id')." AND active = 1
            LIMIT 1";

        $db = new Db_Sql;
        $db->query($sql);

        if (!$db->nextRecord()) {
            return new Account($year);
        }

        return new Account($year, $db->f('id'));

    }

    /**
     * Public: Henter detaljer om konti - sætter lokale variable i klassen
     *
     * @return (integer) id
     * @access private
     */

    function load() {

        if($this->year->get('id') == 0 || $this->id == 0) {
            $this->value['id'] = 0;
            $this->id = 0;
            return 0;
        }

        $db = new DB_Sql;

        $sql = "SELECT
                account.id,
                account.name,
                account.type_key,
                account.use_key,
                account.number,
                account.sum_from_account_number,
                account.sum_to_account_number,
                account.vat_key,
                account.vat_percent,
                account.primosaldo_debet,
                account.primosaldo_credit
            FROM
                accounting_account account
            WHERE account.id = " . $this->id . "
                AND account.intranet_id = ".$this->year->kernel->intranet->get('id'). "
                AND year_id = ".$this->year->get('id')."
            LIMIT 1";

        $db->query($sql);

        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['name'] = $db->f('name');
            //$this->value['comment'] = $db->f('comment');
            $this->value['number'] = $db->f('number');
            $this->value['type_key'] = $db->f('type_key');
            $this->value['type'] = $this->types[$this->value['type_key']];
            $this->value['sum_from'] = $db->f('sum_from_account_number');
            $this->value['sum_to'] = $db->f('sum_to_account_number');
            $this->value['use_key'] = $db->f('use_key');
            $this->value['use'] = $this->use[$this->value['use_key']];
            $this->value['primosaldo_debet'] = $db->f('primosaldo_debet');
            $this->value['primosaldo_credit'] = $db->f('primosaldo_credit');
            $this->value['vat_key'] = $db->f('vat_key');

            // hvis der ikke er moms på året skal alle momsindstillinger nulstilles
            if ($this->year->get('vat') == 0) {
                $this->value['vat_key'] = 0;
                $this->value['vat'] = $this->vat[$db->f('vat_key')];
                $this->value['vat_percent'] = 0;
                $this->value['vat_shorthand'] = 'ingen';

            }
            // hvis der er moms på året
            else {

                $this->value['vat_key'] = $db->f('vat_key');
                $this->value['vat'] = $this->vat[$db->f('vat_key')];
                if ($this->value['vat'] == 'none') {
                    $this->value['vat_percent'] = 0;
                }
                else {
                    $this->value['vat_percent'] = $db->f('vat_percent');
                }

                if ($this->value['vat'] == 'in') {
                    $this->value['vat_account_id'] = $this->year->getSetting('vat_in_account_id');
                }
                elseif ($this->value['vat'] == 'out') {
                    $this->value['vat_account_id'] = $this->year->getSetting('vat_out_account_id');
                }
                else {
                    $this->value['vat_account_id'] = 0;
                }
                $this->value['vat_shorthand'] = $this->value['vat'];
             }
        }
        return $this->get('id');
    }

    /**
     * Public: Opdaterer kontooplysninger
     *
     * @param $var (array) med oplysninger om konto
     */

    function save($var) {

        $var = safeToDb($var);

        // bruges til sumkonti
        if (empty($var['sum_to'])) { $var['sum_to'] = ''; }
        if (empty($var['sum_from'])) { $var['sum_from'] = ''; }

        if (!$this->isNumberFree($var['number'])) {
            $this->error->set('Du kan ikke bruge det samme kontonummer flere gange');
        }

        $validator = new Validator($this->error);
        $validator->isNumeric($var['number'], 'Kontonummeret er ikke et tal');

        $validator->isNumeric($var['type_key'], 'Kontotypen er ikke rigtig');

        if (!array_key_exists($var['type_key'], $this->types)) {
            $this->error->set('Ikke en tilladt type');
        }

         $validator->isNumeric($var['use_key'], 'Det kan en konto ikke bruges til');

        if (!array_key_exists($var['use_key'], $this->use)) {
            $this->error->set('Ikke en tilladt brug af kontoen');
        }

        $validator->isString($var['name'], 'Kontonavnet kan kune være en tekststreng.');
        $validator->isNumeric($var['vat_key'], 'Ugyldig moms', 'allow_empty');
        $validator->isNumeric($var['sum_to'], 'sum_to' , 'allow_empty');
        $validator->isNumeric($var['sum_from'], 'sum_from' , 'allow_empty');

        settype($var['comment'], 'integer');
        $validator->isString($var['comment'], 'Error in comment', '', 'allow_empty');


        if ($this->error->isError()) {
            return false;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE accounting_account ";
            $sql_end = " WHERE id = " . $this->id;
        }
        else {
            $sql_type = "INSERT INTO accounting_account ";
            $sql_end = ", date_created=NOW()";
        }

        $sql = $sql_type . "SET
            number = '".(int)$var['number']."',
            intranet_id = ".$this->year->kernel->intranet->get('id').",
            user_id = ".$this->year->kernel->user->get("id").",
            type_key='".$var['type_key']."',
            year_id = ".$this->year->get('id').",
            use_key = '".$var['use_key']."',
            name = '".$var['name']."',
            comment = '".$var['comment']."',
            vat_percent = '".$var['vat_percent']."',
            sum_to_account_number = '".$var['sum_to']."',
            sum_from_account_number = '".$var['sum_from']."',
            date_changed = NOW(),
            vat_key=".(int)$var['vat_key']." " . $sql_end;

        $db = new DB_Sql;
        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        if (!empty($var['created_from_id']) AND is_numeric($var['created_from_id'])) {
            $db->query("UPDATE accounting_account SET created_from_id = ".$var['created_from_id']." WHERE id = " . $this->id);
        }


        return $this->id;
    }

    /**
     * Funktion til at gemme primosaldoen
     *
     * Denne opdatering af primosaldoen bør ikke gemmes i save(), da det ikke
     * skal være let at ændre primosaldoen.
     *
     * @param (float) $debet
     * @param (float) $credit
     *
     * @access public
     */

    function savePrimosaldo($debet, $credit) {
        if ($this->id == 0) {
            return false;
        }

        $amount = new Amount($debet);
        if (!$amount->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $debet = $amount->get();

        $amount = new Amount($credit);
        if (!$amount->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $credit = $amount->get();


        $debet = (double)$debet;
        $credit = (double)$credit;

        $db = new DB_Sql;
        $db->query("UPDATE accounting_account
            SET
                primosaldo_debet = '".$debet."',
                primosaldo_credit = '".$credit."'
                WHERE id = " . $this->id);
        return true;
    }

    /**
     * Funktion til at slette en konto
     *
     * Skal tjekke om der er poster i året på kontoen.
     *
     * @return 1 on success
     */
    function delete() {
        if ($this->anyPosts()) {
            $this->error->set('Der er poster på kontoen for dette år, så du kan ikke slette den. Næste år kan du lade være med at bogføre på kontoen, og så kan du slette den.');
            return 0;
        }
        $this->getSaldo();
        if ($this->get('saldo') != 0) {
            $this->error->set('Der er registreret noget på primosaldoen på kontoen, så du kan ikke slette den. Du kan slette kontoen, hvis du nulstiller primosaldoen.');
            return 0;
        }

        $db = new DB_Sql;
        $db->query("UPDATE accounting_account SET active = 0, date_changed=NOW() WHERE intranet_id = " . $this->year->kernel->intranet->get('id') . " AND year_id = ".$this->year->get('id')." AND id = " . $this->id);
        return 1;
    }


    /*************************************************************************************
     * VALIDERINGSFUNKTIONER
     ************************************************************************************/


    /**
     * Metoden tjekker om kontoen har den rigtige type, så vi må bogføre på den.
     *
     * @return 1 = yes; 0 = no
     * @access private
     */
    function validForState() {
        if ($this->id > 0) {
            if ($this->get('type_key') == array_search('operating', $this->types) OR $this->get('type_key') == array_search('balance, asset', $this->types) OR $this->get('type_key') == array_search('balance, liability', $this->types)) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Metode til at tjekke om kontonummeret er fri.
     *
     * @see save()
     * @access private
     */

    function isNumberFree($account_number) {
        $account_number = (int)$account_number;

        $db = new DB_Sql;
        $sql = "SELECT
                id
            FROM accounting_account
            WHERE number = " . $account_number . "
                AND intranet_id = " . $this->year->kernel->intranet->get('id') . "
                AND year_id = " .$this->year->get('id'). "
                AND id <> " . $this->id . " AND active = 1";
        $db->query($sql);
        if ($db->numRows() == 0) {
            return true;
        }
        return false;
    }



    /*************************************************************************************
     * SALDOFUNKTIONER
     ************************************************************************************/


    /**
     * Public: Metoden returnerer primosaldoen for en konto
     *
     * @return (array) med debet, credit og total saldo
     */
    function getPrimoSaldo() {
        $sql = "SELECT primosaldo_debet, primosaldo_credit
            FROM accounting_account
            WHERE year_id = " . $this->year->get('id') . "
                AND id = ".$this->id . "
                AND active = 1
                AND intranet_id = ".$this->year->kernel->intranet->get('id');

        $db = new Db_Sql;
        $db->query($sql);

        if (!$db->nextRecord()) {
            return array('debet' => 0, 'credit' => 0, 'saldo' => 0);
        }

        $primo['debet'] = $db->f('primosaldo_debet');
        $primo['credit'] = $db->f('primosaldo_credit');
        $primo['saldo'] = $primo['debet'] - $primo['credit'];

        return $primo;

    }
    /**
     * Public: Metoden returnerer en saldo for en konto
     *
     * Klassen tager højde for primobalancen og den skal også tage højde for
     * sumkonti, se i første omgang getSaldoList().
     *
     * Det vil være for voldsomt at putte
     * den her under get, for så skal saldoen
     * udregnes hver gang jeg skal have fat i
     * et eller andet ved en konto!
     *
     * @param $date_from (date) yyyy-mm-dd Der søges jo kun i indeværende år
     * @param $date_to (date) yyyy-mm-dd   Der søges kun i indeværende år
     *
     * @return (array) med debet, credit og total saldo
     *
     *
     *
     */
    function getSaldo($type = 'stated', $date_from = '', $date_to = '') {
        if (empty($date_from)) {
            $date_from = $this->year->get('from_date');
        }
        if (empty($date_to)) {
            $date_to = $this->year->get('to_date');
        }

        $total_saldo = 0;

        $primo = array(
            'debet' => '',
            'credit' => '',
            'saldo' => ''
        );

        #
        # Tjekker på om datoerne er i indeværende år
        #

        /*
        $validator = new Validator($this->error);
        $validator->isDate($this->year->get("year") . '-' . $date_from, "Fra-datoen er ikke en gyldig dato", "allow_empty");
        $validator->isDate($this->year->get("year") . '-' . $date_to, "Til-datoen er ikke en gyldig dato", "allow_empty");

        if ($this->error->isError()) {
            return 0;
        }
        */

        $db = new DB_Sql;
        /*
        if ($this->get('type_key') == array_search('sum', $this->types)) {
            $sql = "SELECT id FROM accounting_account
                        WHERE number >= " . $this->get('sum_from') . "
                            AND number <= " . $this->get('sum_to') . "
                            AND year_id = ".$this->year->get('id')."
                            AND intranet_id = " . $this->year->kernel->intranet->get('id');
            $db->query($sql);
            $total = 0;
            while ($db->nextRecord()) {
                // $sub = 0;
                $account = new Account($this->year, $db->f('id'));
                $account->getSaldo($date_from, $date_to);
                $total = $total + $account->get('saldo');
            }
            $this->value['saldo'] = $total;
            //$total_saldo = $total_saldo + $total;
        }
        else {
        */
            // henter primosaldoen for kontoen
            $primo = $this->getPrimoSaldo();
            /*
            // henter saldoen for kontoen
            $sql = "SELECT
                    SUM(post.debet) AS debet_total,
                    SUM(post.credit) AS credit_total
                FROM accounting_post post
                INNER JOIN accounting_account account
                    ON account.id = post.account_id
                WHERE account.id = ".$this->id."
                    AND post.year_id = ".$this->year->get('id')."
                    AND post.intranet_id = ".$this->year->kernel->intranet->get('id')."
                    AND DATE_FORMAT(post.date, '%Y-%m-%d') >= '".$date_from."'
                    AND DATE_FORMAT(post.date, '%Y-%m-%d') <= '".$date_to."'
                    AND account.year_id = ".$this->year->get('id');
            */
            $sql = "SELECT
                    SUM(post.debet) AS debet_total,
                    SUM(post.credit) AS credit_total
                FROM accounting_post post
                INNER JOIN accounting_account account
                    ON account.id = post.account_id
                WHERE account.id = ".$this->id."
                    AND post.year_id = ".$this->year->get('id')."
                    AND post.intranet_id = ".$this->year->kernel->intranet->get('id')."
                    AND DATE_FORMAT(post.date, '%Y-%m-%d') >= '".$date_from."'
                    AND DATE_FORMAT(post.date, '%Y-%m-%d') <= '".$date_to."'
                    AND account.year_id = ".$this->year->get('id');

            /*
            $sql = "SELECT
                    SUM(post.debet) AS debet_total,
                    SUM(post.credit) AS credit_total
                FROM accounting_post post
                INNER JOIN accounting_account account
                    ON account.id = post.account_id
                WHERE account.id = ".$this->id."
                    AND post.year_id = ".$this->year->get('id')."
                    AND post.intranet_id = ".$this->year->kernel->intranet->get('id')."
                    AND (post.date BETWEEN '".$date_from."' AND '".$date_to."')
                    AND account.year_id = ".$this->year->get('id');
            */

            if ($type == 'stated') {
                $sql .= ' AND post.stated = 1';
            }
            elseif ($type == 'draft') {
                $sql .= ' AND post.stated = 0';
            }


            $sql .= " GROUP BY post.account_id";

            if ($this->get('type_key') == array_search('sum', $this->types)) {
                $db2 = new DB_Sql;
                $sql = "SELECT id FROM accounting_account
                    WHERE number >= " . $this->get('sum_from') . "
                        AND type_key != ".array_search('sum', $this->types)."
                        AND number <= " . $this->get('sum_to') . "
                        AND year_id = ".$this->year->get('id')."
                        AND intranet_id = " . $this->year->kernel->intranet->get('id');
                $db2->query($sql);
                $total = 0;
                while ($db2->nextRecord()) {
                    // $sub = 0;
                    $sAccount = new Account($this->year, $db2->f('id'));
                    $sAccount->getSaldo();
                    $total = $total + $sAccount->get('saldo');
                }
                $this->value['saldo'] = $total;
                $total_saldo = $total_saldo + $total;
            }
            else {

                $db->query($sql);
                if (!$db->nextRecord()) {
                    $this->value['debet'] = $primo['debet'];
                    $this->value['credit'] = $primo['credit'];
                    $this->value['saldo'] = $this->value['debet'] - $this->value['credit'];
                }
                else {

                    if ($type == 'draft') {
                        $this->value['debet_draft'] = $db->f('debet_total');
                        $this->value['credit_draft'] = $db->f('credit_total');
                        $this->value['saldo_draft'] = $this->value['debet_draft'] - $this->value['credit_draft'];
                    }
                    else {
                        $this->value['debet'] = $primo['debet'] + $db->f('debet_total');
                        $this->value['credit'] = $primo['credit'] + $db->f('credit_total');
                        $this->value['saldo'] = $this->value['debet'] - $this->value['credit'];
                    }
                }
                // Det her kan sikkert laves lidt smartere. Den skal egentlig laves inden
                // alt det ovenover tror jeg - alstå if-sætningen
            }

            return 1;

        //} //if
    }

    /***************************************************************************
     * ØVRIGE METODER
     **************************************************************************/

    /**
     * Returnerer liste med alle kontoerne
     * @param (string) $type Typen af konto, kig i $this->type;
     * @return array
     * @access public
     */

    function getList($type = '', $saldo = false) {
        $type = safeToDb($type);
        $type_sql = '';

        //if($this->year->get('id') == 0 || $this->id == 0) {
        if($this->year->get('id') == 0) {
            //$this->value['id'] = 0;
            //$this->id = 0;
            return array();
        }

        $db = new DB_Sql;
        if (!empty($type)) {
            switch($type) {
                case 'expenses':
                        $type_sql = " AND use_key = '".array_search('expenses', $this->use)."'";
                    break;
                case 'income':
                        $type_sql = " AND use_key = '".array_search('income', $this->use)."'";
                    break;
                case 'finance':
                        $type_sql = " AND use_key = '".array_search('finance', $this->use)."'";
                    break;
                case 'balance':
                    // fall through
                case 'status':
                        $type_sql = " AND (type_key = '".array_search('balance, liability', $this->types)."' OR type_key = '".array_search('balance, asset', $this->types)."')";
                    break;
                case 'drift':
                    // fall through
                case 'operating':
                        $type_sql = " AND type_key = '".array_search('operating', $this->types)."'";
                    break;
                default:
               break;
            }
        }
        $accounts = array();
        $sql = "SELECT
                    account.id,
                    account.number,
                    account.name,
                    account.type_key,
                    account.primosaldo_debet,
                    account.primosaldo_credit,
                    account.created_from_id,
                    account.vat_key,
                    account.sum_from_account_number,
                    account.sum_to_account_number
            FROM accounting_account account
            WHERE intranet_id = ".$this->year->kernel->intranet->get('id')." ".$type_sql."
                AND active = 1 AND year_id = ".$this->year->get('id')." ORDER BY number ASC";

        $db->query($sql);



        $i = 0;
        while ($db->nextRecord()) {
            $accounts[$i]['id'] = $db->f('id');
            $accounts[$i]['name'] = $db->f('name');
            $accounts[$i]['number'] = $db->f('number');
            $accounts[$i]['type_key'] = $db->f('type_key');
            $accounts[$i]['type'] = $this->types[$db->f('type_key')];
            $accounts[$i]['sum_from'] = $db->f('sum_from_account_number');
            $accounts[$i]['sum_to'] = $db->f('sum_to_account_number');


            $accounts[$i]['primosaldo_debet'] = $db->f('primosaldo_debet');
            $accounts[$i]['primosaldo_credit'] = $db->f('primosaldo_credit');
            $accounts[$i]['created_from_id'] = $db->f('created_from_id');
            $accounts[$i]['vat_shorthand'] = $this->vat[$db->f('vat_key')];

            if ($saldo === true) {
                $account = new Account($this->year, $db->f('id'));
                $account->getSaldo();
                $accounts[$i]['debet'] = $account->get('debet');
                $accounts[$i]['credit'] = $account->get('credit');
                $accounts[$i]['saldo'] = $account->get('saldo');

            }


            $i++;
        }
        return $accounts;
    }

    function anyAccounts() {
        $db = new DB_Sql;
        $sql = "SELECT id
            FROM accounting_account
            WHERE intranet_id = " . $this->year->kernel->intranet->get("id") . " AND year_id = ".$this->year->get('id')." AND active = 1";
        $db->query($sql);

        return $db->numRows();

    }

    function anyPosts() {
        $db = new DB_Sql;
        $db->query("SELECT
                id
            FROM accounting_post post
            WHERE (post.account_id = ". $this->id . ")
                AND intranet_id = ".$this->year->kernel->intranet->get('id')."
                AND year_id = " . $this->year->get('id') . "
                LIMIT 1");
        return $db->numRows();
    }

    function getPosts() {
        $posts = array();

        if ($this->id == 0) {
            return $posts;
        }
        $db2 = new DB_Sql;

        $db2->query("SELECT
                    id,
                    date,
                    DATE_FORMAT(date, '%d-%m-%Y') AS dk_date,
                    voucher_id,
                    text,
                    debet,
                    credit
                FROM accounting_post post
                WHERE (post.account_id = ". $this->get('id') . ")
                    AND intranet_id = ".$this->year->kernel->intranet->get('id')."
                    AND year_id = " . $this->year->get('id') . "
                    AND stated = 1
                    ORDER BY date ASC, id ASC");
        $i = 1;
        while ($db2->nextRecord()) {
                  $posts[$i]['id'] = $db2->f('id');
                  $posts[$i]['dk_date'] = $db2->f('dk_date');
                  $posts[$i]['date'] = $db2->f('date');
                  $posts[$i]['voucher_id'] = $db2->f('voucher_id');
                $voucher = new Voucher($this->year, $db2->f('voucher_id'));
                  $posts[$i]['voucher_number'] = $voucher->get('number');
                  $posts[$i]['text'] = $db2->f('text');
                  $posts[$i]['debet'] = $db2->f('debet');
                  $posts[$i]['credit'] = $db2->f('credit');
                  $posts[$i]['stated'] = $db2->f('stated');
                $posts[$i]['account_id'] = $db2->f('account_id');
                /*
                $account = new Account($this->year, $db2->f('account_id'));
                $posts[$i]['account_number'] = $account->get('number');
                */
                $i++;

        } // while
        return $posts;
    }

    /**
     * Calculates the vat amount
     * 
     * @link http://eforum.idg.se/viewmsg.asp?EntriesId=831525
     *
     * @param float $amount      Amount
     * @param float $vat_percent Vat percent
     * 
     * @return float Vat amount
     */
    function calculateVat($amount, $vat_percent) {
        $amount = (float)$amount;
        $vat_percent = (float)$vat_percent / 100;

        return $amount * ($vat_percent / (1 + $vat_percent));
    }
}
?>