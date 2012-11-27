<?php
/**
 * Meget af det der er i year_end tabellen kan vist lige så godt indkorporeres i selve årstabellen
 * @package Intraface_Accounting
 */
class YearEnd extends Intraface_Standard
{
    public $error;
    public $value;
    public $year;

    /*
    protected $step = array(
        1 => 'Er alle poster i året indtastet?',
        2 => 'Har du lavet momsregnskab og opgivet det til Skat?',
        3 => 'Vælg resultatkonto og overfør posterne til resultatkontoen',
        4 => 'Rapport med årsregnskabet'
    );
    */

    // disse typer bruges i forbindelse med om statements er drift eller status
    public $types = array(
        1 => 'operating',
        2 => 'balance'
    );

    public $actions = array(
        1 => 'operating_reset',
        2 => 'result_account_reset'
    );

    function __construct($year)
    {
        if (!is_object($year)) {
            throw new Exception('Year::__construct: Ikke et gyldigt Year object');
        }
        $this->year = $year;
        $this->error = new Intraface_Error;
        if (!$this->load()) {
            $this->start();
        }
    }

    private function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM accounting_year_end WHERE year_id = " . $this->year->get('id') . " AND intranet_id =" . $this->year->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return 0;
        }
        $this->value['id'] = $db->f('id');
        $this->value['step_key'] = $db->f('step_key');
        $this->value['step'] = $db->f('step_key');
        $this->value['result_account_reset_voucher_id'] = $db->f('result_account_reset_voucher_id');
        $this->value['operating_reset_voucher_id'] = $db->f('operating_reset_voucher_id');
        return $db->f('id');
    }

    function start()
    {
        if ($this->get('id') > 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("INSERT INTO accounting_year_end SET date_created=NOW(), year_id = " . $this->year->get('id') . ", intranet_id =" .$this->year->kernel->intranet->get('id'));
        return true;
    }

    function setStep($step)
    {
        $db = new DB_Sql;
        $db->query("UPDATE accounting_year_end SET date_updated=NOW(), step_key = " . (int)$step . " WHERE year_id = " . $this->year->get('id'));
        return true;
    }

    function setStated($action, $voucher_id)
    {
        $db = new DB_Sql;

        switch ($action) {
            // bruges i forbindelse med nulstilling af resultatkontoen
            case 'operating_reset':
                $db->query("UPDATE accounting_year_end SET date_updated=NOW(), operating_reset_voucher_id = " . (int)$voucher_id . " WHERE year_id = " . $this->year->get('id'));
                return true;
                break;
            // bruges i forbindelse med overførelse af kapitalkontoen
            case 'result_account_reset':
                $db->query("UPDATE accounting_year_end SET date_updated=NOW(), result_account_reset_voucher_id = " . (int)$voucher_id . " WHERE year_id = " . $this->year->get('id'));
                return true;
                break;

            default:
                throw new Exception('YearEnd::setStated: Ugyldig type');
                break;
        }

    }

    /**
     * Denne funktion skal gemme de ændringer der bliver lavet i bogføringen
     *
     * Det betyder at vi har mulighed for at vende den igen - skal i så tilfælde bogføres
     * på samme bilag med modposteringer foran.
     *
     * debet-konto
     * credit-konto
     * amount
     *
     */
    function saveStatedAction($action, $voucher_id, $debet_account_id, $credit_account_id, $amount)
    {
        $db = new Db_Sql;
        $db->query("INSERT INTO accounting_year_end_action SET date_created = NOW(), voucher_id = ".$voucher_id.", debet_account_id = ".$debet_account_id.", credit_account_id = ".$credit_account_id.", amount=".$amount.", intranet_id=".$this->year->kernel->intranet->get('id').", type_key = ".array_search($action, $this->actions).", year_id = ".$this->year->get('id'));
        return true;
    }

    function deleteStatedAction($id)
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM accounting_year_end_action WHERE id = " . (int)$id);
        return true;
    }

    function getStatedActions($action)
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM accounting_year_end_action WHERE year_id = " . $this->year->get('id') . " AND type_key = ".array_search($action, $this->actions)." AND intranet_id = " . $this->year->kernel->intranet->get('id'));
        $actions = array();
        $i = 0;
        while ($db->nextRecord()) {
            $actions[$i]['id'] = $db->f('id');
            $actions[$i]['voucher_id'] = $db->f('voucher_id');
            $actions[$i]['debet_account_id'] = $db->f('debet_account_id');
            $actions[$i]['credit_account_id'] = $db->f('credit_account_id');
            $actions[$i]['amount'] = $db->f('amount');

            $i++;
        }

        return $actions;
    }

    function flushStatement($type)
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM accounting_year_end_statement WHERE intranet_id = ".$this->year->kernel->intranet->get('id')." AND year_id = " . $this->year->get('id') . " AND type_key = " . array_search($type, $this->types));
        return true;
    }

    /**
     * Gemme resultatopgørelsen
     *
     * @todo Der er problemer hvis man gemmer resultatopgørelsen flere gange (hvis man også nulstiller kontiene), så det
     * bør vel egentlig ikke være muligt! Måske kan man forestille sig, at den så bare
     * gemmer videre og at den ved get lægger tallene i amount sammen?
     *
     */
    function saveStatement($type)
    {
        $this->flushStatement($type);

        switch ($type) {
            case 'operating':
                // disse konti bliver nulstillet, så vi kan ikke bare gemme løs. Hvad gør vi ved det?
                $account_start = $this->getAccount($this->year->getSetting('result_account_id_start'));
                $account_end = $this->getAccount($this->year->getSetting('result_account_id_end'));
                break;
            case 'balance':
                // her kunne det måske være en ide at flushe
                // for de bliver ikke nulstillet
                $account_start = $this->getAccount($this->year->getSetting('balance_account_id_start'));
                $account_end = $this->getAccount($this->year->getSetting('balance_account_id_end'));
                break;
            default:
                throw new Exception('YearEnd::getStatement: Ugyldig type');
                break;

        }

        $db = new DB_Sql;
        $db2 = new DB_Sql;

        $db->query("SELECT id FROM accounting_account WHERE year_id = " .$this->year->get('id'). " AND intranet_id = " . $this->year->kernel->intranet->get('id') . " AND number >= " . $account_start->get('number') . " AND number <= " . $account_end->get('number') . " ORDER BY number ASC");

        while ($db->nextRecord()) {
            $account = new Account($this->year, $db->f('id'));
            $account->getSaldo();

            // vi vender sgu lige fortegnet, så udgifter får negativt fortegn
            // og indtægter positivt fortegn
            $db2->query("INSERT INTO accounting_year_end_statement SET type_key = ".array_search($type, $this->types).", intranet_id = ".$this->year->kernel->intranet->get('id').", year_id = ".$this->year->get('id').", account_id = " . $account->get('id') . ", amount = '".-1 * $account->get('saldo')."'");
        }

        return true;
    }

    function getAccount($id = 0)
    {
        return new Account($this->year, $id);
    }

    function getStatement($type)
    {

        switch ($type) {
            case 'operating':
                break;
            case 'balance':
                break;
            default:
                throw new Exception('YearEnd::getStatement: Ugyldig type');
                break;
        }

        // @todo hvis jeg kunne få den her til at håndtere summen af det der er gemt,
        // så kunne jeg måske gøre noget rigtig smart?
        $db = new DB_Sql;
        $db->query("SELECT * FROM accounting_year_end_statement WHERE year_id = ".$this->year->get('id')." AND intranet_id = ".$this->year->kernel->intranet->get('id')." AND type_key = ".array_search($type, $this->types)." ORDER BY id ASC");
        $i = 0;
        while ($db->nextRecord()) {
            $account = new Account($this->year, $db->f('account_id'));
            $statement[$i]['name'] = $account->get('name');
            $statement[$i]['number'] = $account->get('number');
            $statement[$i]['saldo'] = $db->f('amount');
            $statement[$i]['type'] = $account->get('type');

            $i++;
        }
        return $statement;
    }

    /**
     *
     * @param $type (kan være do og reverse) - reverse er hvis man fortryder at man har gemt
     *				dog skal det jo stadig bogføres
     */
    function resetOperatingAccounts($type = 'do')
    {
        switch($type) {
            case 'do':
                break;
            case 'reverse':
                break;
            default:
                    throw new Exception('YearEnd::resetOperatingAccounts ugyldig type');
                break;
        }

        if ($this->year->getSetting('result_account_id') <= 0) {
            $this->error->set('Resultatkontoen er ikke sat');
        }

        if ($this->error->isError()) {
            return false;
        }
        $account = $this->getAccount();
        $result_account = $this->getAccount($this->year->getSetting('result_account_id'));

        switch ($type) {
            case 'reverse':
                // hvis man vil reverse skal vi finde actions
                // vi skal lave bogføringen
                // og derefter slette actions igen.

                if ($this->get('operating_reset_voucher_id') == 0) {
                    $this->error->set('Du kan ikke lave en reversep� noget der ikke er bogf�rt');
                }

                $voucher = new Voucher($this->year, $this->get('operating_reset_voucher_id'));

                $actions = $this->getStatedActions('operating_reset');

                if (!is_array($actions) OR count($actions) == 0) {
                    $this->error->set('Du kan ikke lave en reverse, n�r der ikke er gemt nogen actions');
                }

                if ($this->error->isError()) {
                    $this->error->view();
                    return 0;
                }

                foreach ($actions AS $a) {

                    $save_array = array();
                    // der er byttet om på debet og credit med vilje, fordi
                    // det skal reverses
                    $debet_account = new Account($this->year, $a['credit_account_id']);
                    $credit_account = new Account($this->year, $a['debet_account_id']);

                    $save_array = array(
                        'date' => $this->year->get('to_date_dk'),
                        'debet_account_number' => $debet_account->get('number'),
                        'credit_account_number' => $credit_account->get('number'),
                        'amount' => amountToForm($a['amount']),
                        'text' => 'Modpostering: ' . $debet_account->get('name') . ' og ' . $credit_account->get('name'),
                        'vat_off' => 1

                    );

                    if (!empty($save_array)) {
                        if ($voucher->saveInDayBook($save_array, true)) {
                            $this->deleteStatedAction($a['id']);
                        } else {
                            $voucher->error->view();
                        }
                    }
                }
            break;
            default:

                $voucher = new Voucher($this->year, $this->get('operating_reset_voucher_id'));
                $voucher->save(array(
                    'date' => $this->year->get('to_date_dk'),
                    'text' => 'Årsafslutning. Overførsel af driftskonti til resultatopgørelse'
                ));

                $accounts = $account->getList('operating', true);
                if (!is_array($accounts) OR count($accounts) == 0){
                    $this->error->set('Du kan ikke nulstille nogle konti der ikke findes');
                    return false;
                }

                foreach ($accounts AS $a) {
                    $save_array = array();
                    $account = new Account($this->year, $a['id']);
                    $account->getSaldo();

                    if ($account->get('saldo') > 0) {

                        $save_array = array(
                            'date' => $this->year->get('to_date_dk'),
                            'debet_account_number' => $result_account->get('number'),
                            'credit_account_number' => $account->get('number'),
                            'amount' => amountToForm(abs($account->get('saldo'))), // amountToFrom necessary to get the correct format for daybook
                            'text' => $account->get('name') . ' til resultatkontoen',
                            'vat_off' => 1

                        );
                        $debet_account = $result_account;
                        $credit_account = $account;
                    } elseif ($account->get('saldo') <= 0) {
                        $save_array = array(
                            'date' => $this->year->get('to_date_dk'),
                            'debet_account_number' => $account->get('number'),
                            'credit_account_number' => $result_account->get('number'),
                            'amount' => amountToForm(abs($account->get('saldo'))), // amountToFrom necessary to get the correct format for daybook
                            'text' => $account->get('name') . ' til resultatkontoen',
                            'vat_off' => 1
                        );
                        $debet_account = $account;
                        $credit_account = $result_account;

                    }

                    if (!empty($save_array)) {
                        if ($voucher->saveInDayBook($save_array, true)) {
                            $this->saveStatedAction('operating_reset', $voucher->get('id'), $debet_account->get('id'), $credit_account->get('id'), abs(amountToForm($account->get('saldo'))));
                        }
                    }
                    $this->setStated('operating_reset', $voucher->get('id'));

                }
                return true;
            break;
        }
    }

    function resetYearResult($type = 'do')
    {
        switch($type) {
            case 'do':
                // her sker ikke noget
                break;
            case 'reverse':
                // her sker ikke noget
                break;
            default:
                throw new Exception('YearEnd::resetOperatingAccounts ugyldig type');
                break;
        }

        if ($this->year->getSetting('result_account_id') <= 0) {
            $this->error->set('Resultatkontoen er ikke sat');
        }

        if ($this->year->getSetting('capital_account_id') <= 0) {
            $this->error->set('Kapitalkontoen er ikke sat');
        }


        if ($this->error->isError()) {
            return false;
        }

        switch ($type) {
            case 'reverse':
                // hvis man vil reverse skal vi finde actions
                // vi skal lave bogføringen
                // og derefter slette actions igen.

                if ($this->get('result_account_reset_voucher_id') == 0) {
                    $this->error->set('Du kan ikke lave en reverse på noget der ikke er bogført');
                }

                $voucher = new Voucher($this->year, $this->get('result_account_reset_voucher_id'));

                $actions = $this->getStatedActions('result_account_reset');


                if (!is_array($actions) OR count($actions) == 0) {
                    $this->error->set('Du kan ikke lave en reverse, når der ikke er gemt nogen actions');
                }

                if ($this->error->isError()) {
                    return false;
                }

                foreach ($actions AS $a) {

                    $save_array = array();
                    // der er byttet om på debet og credit med vilje, fordi
                    // det skal reverses
                    $debet_account = new Account($this->year, $a['credit_account_id']);
                    $credit_account = new Account($this->year, $a['debet_account_id']);

                    $save_array = array(
                        'date' => $this->year->get('to_date_dk'),
                        'debet_account_number' => $debet_account->get('number'),
                        'credit_account_number' => $credit_account->get('number'),
                        'amount' => amountToForm($a['amount']),
                        'text' => 'Modpostering: ' . $debet_account->get('name') . ' og ' . $credit_account->get('name'),
                        'vat_off' => 1

                    );

                    if (!empty($save_array)) {
                        if ($voucher->saveInDayBook($save_array, true)) {
                            $this->deleteStatedAction($a['id']);
                        }
                    }
                }
                return true;
            break;
            default:
                if (!$this->get('result_account_reset_voucher_id')) {
                    $voucher = new Voucher($this->year);
                    $voucher->save(array(
                        'date' => $this->year->get('to_date_dk'),
                        'text' => 'Årsafslutning. Årets resultat overføres til egenkapitalen'
                    ));
                } else {
                    $voucher = new Voucher($this->year, $this->get('result_account_reset_voucher_id'));
                }

                $result_account = new Account($this->year, $this->year->getSetting('result_account_id'));
                $result_account->getSaldo();
                $capital_account = new Account($this->year, $this->year->getSetting('capital_account_id'));

                $save_array = array();
                if ($result_account->get('saldo') < 0) {

                    $save_array = array(
                        'date' => $this->year->get('to_date_dk'),
                        'debet_account_number' => $result_account->get('number'),
                        'credit_account_number' => $capital_account->get('number'),
                        'amount' => abs(amountToForm($result_account->get('saldo'))), // amountToFrom necessary to get the correct format for daybook
                        'text' => $result_account->get('name') . ' nulstilles',
                        'vat_off' => 1

                    );
                    $debet_account = $result_account;
                    $credit_account = $capital_account;
                } elseif ($result_account->get('saldo') >= 0) {
                    $save_array = array(
                        'date' => $this->year->get('to_date_dk'),
                        'debet_account_number' => $capital_account->get('number'),
                        'credit_account_number' => $result_account->get('number'),
                        'amount' => abs(amountToForm($result_account->get('saldo'))), // amountToFrom necessary to get the correct format for daybook
                        'text' => $result_account->get('name') . ' nulstilles',
                        'vat_off' => 1
                    );
                    $debet_account = $capital_account;
                    $credit_account = $result_account;

                }

                if (!empty($save_array)) {
                    if ($voucher->saveInDayBook($save_array, true)) {
                        $this->saveStatedAction('result_account_reset', $voucher->get('id'), $debet_account->get('id'), $credit_account->get('id'), abs(amountToForm($result_account->get('saldo'))));
                    } else {
                        $voucher->error->view();
                    }
                }
                $this->setStated('result_account_reset', $voucher->get('id'));
                return true;
            break;
        }
    }
}
