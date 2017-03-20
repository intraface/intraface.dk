<?php
/**
 * Voucher
 *
 * @package Intraface_Accounting
 * @author  Lars Olesen
 * @since   1.0
 * @version     1.0
 */
require_once 'Intraface/modules/accounting/Account.php';
require_once 'Intraface/modules/accounting/Post.php';

class Voucher extends Intraface_Standard
{
    private $id; // integer
    public $year; // object
    public $error; // object
    public $value; // array
    private $vatpercent; // float

    /**
     * Constructor
     *
     * @param object  $year_object
     * @param integer $post_id (optional)
     *
     * @return void
     */
    function __construct($year_object, $id = 0)
    {
        $this->error      = new Intraface_Error;
        $this->year       = $year_object;
        $this->id         = (int)$id;
        $this->vatpercent = $this->year->kernel->getSetting()->get('intranet', 'vatpercent');

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Creates a voucher for the voucher number
     *
     * @deprecated
     * @param object $year
     * @param string $voucher_number
     *
     * @return void
     */
    function factory($year, $voucher_number)
    {
        $gateway = new Intraface_modules_accounting_VoucherGateway($year);
        return $gateway->findFromVoucherNumber($voucher_number);
    }

    /**
     * Loads data
     *
     * @return boolean
     */
    private function load()
    {
        $sql = "SELECT
                    voucher.id AS id,
                    voucher.number,
                    voucher.text,
                    DATE_FORMAT(voucher.date, '%d-%m-%Y') AS date_dk,
                    voucher.date,
                    voucher.reference
            FROM accounting_voucher voucher
            WHERE voucher.id = " . $this->id. " AND intranet_id = ". $this->year->kernel->intranet->getId();

        $db = new DB_Sql;
        $db->query($sql);

        if (!$db->nextRecord()) {
            return false;
        }
        $this->value['id'] = $db->f('id');
        $this->value['number'] = $db->f('number');
        $this->value['text'] = $db->f('text');
        $this->value['reference'] = $db->f('reference');
        $this->value['date'] = $db->f('date');
        $this->value['date_dk'] = $db->f('date_dk');

        return true;
    }

    /**
     * Valideringsfunktioner
     *
     * @param array $var Array to validate
     *
     * @return boolean
     */
    function validate($var)
    {
        $validator = new Intraface_Validator($this->error);
        if (!empty($var['voucher_number'])) {
            $validator->isNumeric($var['voucher_number'], 'Voucher er ikke et tal', 'allow_empty');
        }
        if (!empty($var['reference'])) {
            $validator->isString($var['reference'], 'Reference er ikke en streng', '', 'allow_empty');
        }
        $validator->isString($var['text'], 'Beskrivelsen skal være en tekststreng');

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Updates voucher
     *
     * You can edit the voucher, but not touch the posts when they are stated
     *
     * @param array $var With information
     *
     * @return 0 = error; 1 = success
     */
    public function save($var)
    {
        if (empty($var['reference'])) {
            $var['reference'] = '';
        }

        $var = safeToDb($var);

        $post_date = new Intraface_Date($var['date']);
        $post_date->convert2db();

        if (empty($var['voucher_number'])) {
            $var['voucher_number'] = $this->getMaxNumber() + 1;
        }

        if (!$this->validate($var)) {
            return 0;
        }

        if (empty($this->id)) {
            $sql_type = "INSERT INTO";
            $sql_end = ", date_created = NOW()";
        } else {
            $sql_type = "UPDATE";
            $sql_end = " WHERE id = " . (int)$this->id;
        }

        $db = new DB_Sql;
        $sql = $sql_type . " accounting_voucher
            SET intranet_id = ".$this->year->kernel->intranet->get('id').",
                year_id = ".$this->year->get('id').",
                user_id = ".$this->year->kernel->user->get('id').",
                date_updated = NOW(),
                number = '".$var['voucher_number']."',
                date = '".$post_date->get()."',
                reference = '".$var['reference']."',
                text = '".$var['text']."'" . $sql_end;

        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        $this->load();

        return $this->id;
    }

    function saveInDaybook($var, $skip_draft = false)
    {
        $var = safeToDb($var);

        $post_date = new Intraface_Date($var['date']);
        if (!$post_date->convert2db()) {
            $this->error->set('Kunne ikke konvertere datoen');
        }

        $validator = new Intraface_Validator($this->error);
        $validator->isNumeric($var['debet_account_number'], 'Debetkontoen er ikke et tal');
        $validator->isNumeric($var['credit_account_number'], 'Kreditkontoen er ikke et tal');
        $validator->isDouble($var['amount'], 'Beløbet skal være et tal');
        settype($var['vat_off'], 'integer');
        if ($var['vat_off'] != 0 and $var['vat_off'] != 1) {
            $this->error->set('vat_off');
        }

        if (!$this->year->isYearOpen()) {
            $this->error->set('Dette år er ikke åbent til bogføring');
        } elseif (!$this->year->isDateInYear($post_date->get())) {
            $this->error->set('Denne dato er ikke i det pågældende år');
        }

        if (empty($var['vat_off'])) {
            $var['vat_off'] = 0;
        }
        if (empty($var['debet_account_number'])) {
            $var['debet_account_number'] = 0;
        }
        if (empty($var['credit_account_number'])) {
            $var['credit_account_number'] = 0;
        }

        $amount = new Intraface_Amount($var['amount']);
        if (!$amount->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $var['amount'] = $amount->get();

        // Treat vales for save
        $var = safeToDb($var);

        if (!$this->validate($var)) {
            return 0;
        }

        $debetaccount = $this->getAccount($var['debet_account_number']);

        if (!$debetaccount->validForState()) {
            $this->error->set('Du kan ikke bogføre på den valgte debetkonto');
        }

        $creditaccount = $this->getAccount($var['credit_account_number']);

        if (!$creditaccount->validForState()) {
            $this->error->set('Du kan ikke bogføre på den valgte kreditkonto');
        }

        if ($this->error->isError()) {
            return 0;
        }

        // if already found, do not save ahead of time
        if ($this->id == 0) {
            $this->save($var);
        }

        $this->value['text'] = $var['text'];
        $this->value['amount'] = $var['amount'];
        $this->value['date'] = $post_date->get();
        $this->value['debet_account_number'] = $debetaccount->get('number');
        $this->value['debet_account_id'] = $debetaccount->get('id');
        $this->value['debet_account_name'] = $debetaccount->get('name');
        $this->value['credit_account_id'] = $creditaccount->get('id');
        $this->value['credit_account_number'] = $creditaccount->get('number');
        $this->value['credit_account_name'] = $creditaccount->get('name');
        $this->value['vat_off'] = $var['vat_off'];
        $this->value['saldo'] = 0;

        $this->state($skip_draft);

        return $this->id;
    }

    protected function getAccount($number)
    {
        $gateway = new Intraface_modules_accounting_AccountGateway($this->year);
        return $gateway->findFromNumber($number);
    }

    /**
     * @return boolean
     */
    function delete()
    {
        return true;
    }

    /**
     * @return array
     */

    function getList($filter = '')
    {
        $gateway = new Intraface_modules_accounting_VoucherGateway($this->year);
        return $gateway->getList($filter);
    }

    /**
     * States voucher
     *
     * @param boolean $skip_draft if daybook is to be skipped
     */
    function state($skip_draft = false)
    {
        // Bogføring af almindeligt køb i Danmark
        // Ifølge det dobbelte bogholderis princip skal alle poster bogføres på mindst to
        // konti.

        // debetkontoen
        $this->_stateHelper($this->get('date'), $this->get('text'), $this->get('debet_account_id'), $this->get('amount'), 0, $this->get('vat_off'), $skip_draft);

        // kreditkontoen
        $this->_stateHelper($this->get('date'), $this->get("text"), $this->get('credit_account_id'), 0, $this->get('amount'), $this->get('vat_off'), $skip_draft);

        // Varekøb i udlandet
        // Der skal udregnes moms af alle varekøb i udlandet, og de skal bogføres
        // på den tilhørende konto

        $buy_abroad = unserialize($this->year->getSetting('buy_abroad_accounts'));
        $buy_eu = unserialize($this->year->getSetting('buy_eu_accounts'));

        if (is_array($buy_eu) and is_array($buy_abroad)) {
            $buy_all_abroad = array_merge($buy_abroad, $buy_eu);
        } elseif (is_array($buy_eu)) {
            $buy_all_abroad = $buy_eu;
        } elseif (is_array($buy_abroad)) {
            $buy_all_abroad = $buy_abroad;
        }

        $amount = $this->get('amount') * ($this->vatpercent / 100);

        // I det omfang du har fradragsret for momsen, kan du medregne det beregnede
        // momsbeløb til konto for indgående moms. Det beregnede momsbeløb af EU-varekøb
        // behandles dermed på samme måde som momsen af varekøb foretaget i Danmark.

        if ($this->get('vat_off') == 0) {
            // gemme moms hvis det er nødvendigt
            if (isset($buy_all_abroad) && is_array($buy_all_abroad) and in_array($this->get('debet_account_id'), $buy_all_abroad)) {
                // så skal beløbet ganges med momsprocenten og smides på moms af varekøb i udlandet
                $credit = new Post($this);
                $credit->save($this->get('date'), $this->year->getSetting('vat_abroad_account_id'), 'Moms af varekøb i udland', 0, $amount, $skip_draft);
                $debet = new Post($this);
                $debet->save($this->get('date'), $this->year->getSetting('vat_in_account_id'), 'Moms af varekøb i udland', $amount, 0, $skip_draft);
            } elseif (!empty($buy_all_abroad) and is_array($buy_all_abroad) and in_array($this->get('credit_account_id'), $buy_all_abroad)) {
                // tilbageføring af moms hvis nødevndigt
                // så skal beløbet ganges med momsprocenten og smides på moms af varekøb i udlandet
                $debet = new Post($this);
                $debet->save($this->get('date'), $this->year->getSetting('vat_abroad_account_id'), 'Tilbageført: Moms af varekøb i udland', $amount, 0, $skip_draft);
                $credit = new Post($this);
                $credit->save($this->get('date'), $this->year->getSetting('vat_in_account_id'), 'Tilbageført: Moms af varekøb i udland', 0, $amount, $skip_draft);
            }
        }
        return true;
    }


    /**
     * Prepares amounts for stating, e.g. whether the accounts needs vat calculations
     *
     * @param integer $year_id
     * @param string  $date
     * @param string  $voucher_number
     * @param string  $text
     * @param integer $account_id
     * @param float   $debet
     * @param float   $credit
     *
     * @return boolean
     */
    private function _stateHelper($date, $text, $account_id, $debet, $credit, $vat_off = 0, $skip_draft = false)
    {

        $text = safeToDb($text);
        $vat_percent = $this->vatpercent;

        // Kontoen
        $account = new Account($this->year, $account_id);
        $vat_account_id = $account->get('vat_account_id');

        // Konti uden moms
        if ($vat_off == 1) {
            $post = new Post($this);
            $post->save($date, $account_id, $text, $debet, $credit, $skip_draft);
        } elseif ($vat_off == 0) {
            // Konti med moms

            // Hvis der er moms på kontoen skal den trækkes fra beløbet først
            switch ($account->get('vat')) {
                case 'in': // indgående moms - købsmoms
                    if ($debet > 0) { // bogfør til momskonto hvis det er et debet-beløb
                        $vat_amount = $this->calculateVat($debet, $vat_percent);
                        $debet = $debet - $vat_amount;
                        // bogfør momsen
                        $post = new Post($this);
                        $post->save($date, $vat_account_id, $text . " - købsmoms", $vat_amount, 0, $skip_draft);
                    } else {
                        $vat_amount = $this->calculateVat($credit, $vat_percent);
                        $credit = $credit - $vat_amount;
                        // bogføre udgående moms
                        $post = new Post($this);
                        $post->save($date, $vat_account_id, $text . " - tilbageført moms", 0, $vat_amount, $skip_draft);
                    }

                        // bogføre selve posten
                        $post = new Post($this);
                        $post->save($date, $account_id, $text, $debet, $credit, $skip_draft);

                    break;

                // udgående moms
                case 'out': // Bogfør til momskonto hvis det er et credit beløb
                    if ($credit > 0) {
                        $vat_amount = $this->calculateVat($credit, $vat_percent);
                        $credit = $credit - $vat_amount;
                        // bogføre udgående moms
                        $post = new Post($this);
                        $post->save($date, $vat_account_id, $text . " - salgsmoms", 0, $vat_amount, $skip_draft);
                    } else {
                        // tilbagefører momsen hvis det er et debet beløb
                        $vat_amount = $this->calculateVat($debet, $vat_percent);
                        $debet = $debet - $vat_amount;

                        // bogføre momsen
                        $post = new Post($this);
                        $post->save($date, $vat_account_id, "Tilbageført moms", $vat_amount, 0, $skip_draft);
                    }

                        // bogføre selve posten
                        $post = new Post($this);
                        $post->save($date, $account_id, $text, $debet, $credit, $skip_draft);
                    break;

                // hvis kontoen ikke er en momskonto
                default:
                        // bogføre bilaget hvor der ikke er moms
                        $post = new Post($this);
                        $post->save($date, $account_id, $text, $debet, $credit, $skip_draft);
                    break;
            }
        }

        return true;
    }

    /**
     * Bogfører de poster, der er i kassekladden
     *
     * Klassen vælger automatisk alle poster i kladden og bogfører dem en efter en.
     * De poster der kan bogføres, mens de resterende ikke bogføres.
     * Der laves igen tjek på, om året er åbent og om datoen for posten er i året.
     *
     * @return boolean
     */
    public function stateDraft()
    {
        if (!$this->year->vatAccountIsSet()) {
            $this->error->set('Du skal først sætte momskonti, inden du kan bogføre.');
        }

        if ($this->error->isError()) {
            return false;
        }

        $post = new Post($this);
        $posts = $post->getList('draft');
        if (!is_array($posts) or count($posts) == 0) {
            $this->error->set('Der var ikke nogen poster at bogføre');
            return false;
        }

        foreach ($posts as $p) {
            $post = new Post($this, $p['id']);

            if (!$post->setStated()) {
                $this->error->set('id#' .$p['id'] . ': Det lykkedes ikke at bogføre denne post.');
            }

            // tjekker om der har været nogle fejl i bogføringen
            if ($this->error->isError()) {
                //$this->error->view();
                return false;
            }
        }

        return true;
    }

    function stateVoucher()
    {
        if (!$this->year->vatAccountIsSet()) {
            $this->error->set('Du skal først sætte momskonti, inden du kan bogføre.');
        }

        if ($this->id == 0) {
            $this->error->set('Kan kun bogføre et bilag, hvis den har et id');
        }

        if ($this->get('saldo') <> 0) {
            $this->error->set('Du kan kun bogføre et bilag, hvis det stemmer. Saldoen på dette bilag er ' . $this->get('saldo') . '.');
        }

        if ($this->error->isError()) {
            return false;
        }

        $posts = $this->getPosts();

        foreach ($posts as $p) {
            $post = new Post($this, $p['id']);
            if ($post->get('stated') == 1) {
                continue;
            }

            if (!$post->setStated()) {
                $this->error->set('id#' .$p['id'] . ': Det lykkedes ikke at bogføre denne post.');
            }
        }
        // tjekker om der har været nogle fejl i bogføringen
        if ($this->error->isError()) {
            //$this->error->view();
            return false;
        }
        return true;
    }

    function getPosts()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, text, debet, credit, account_id, stated, date, DATE_FORMAT(date, '%d-%m-%Y') AS date_dk FROM accounting_post WHERE voucher_id = " . $this->id . " AND intranet_id=".$this->year->kernel->intranet->get('id'));
        $list = array();
        $i = 0;
        $this->value['saldo'] = 0;
        while ($db->nextRecord()) {
            $list[$i]['id'] = $db->f('id');
            $list[$i]['date_dk'] = $db->f('date_dk');
            $list[$i]['date'] = $db->f('date');
            $list[$i]['text'] = $db->f('text');
            $list[$i]['debet'] = $db->f('debet');
            $list[$i]['credit'] = $db->f('credit');
            $list[$i]['voucher_number'] = $this->get('number');
            $list[$i]['reference'] = $this->get('reference');
            $list[$i]['voucher_id'] = $this->get('id');
            $list[$i]['account_id'] = $db->f('account_id');
            $list[$i]['stated'] = $db->f('stated');
            $account = new Account($this->year, $db->f('account_id'));
            $list[$i]['account_number'] = $account->get('number');
            $list[$i]['account_name'] = $account->get('name');

            $this->value['saldo'] += $db->f('debet');
            $this->value['saldo'] -= $db->f('credit');

            $i++;
        }
        return $list;
    }

    /**
     * Udregner momsbeløbet
     *
     * @deprecated
     *
     * @param float $amount
     * @param float $vat_percent
     *
     * @return float
     */
    public function calculateVat($amount, $vat_percent)
    {
        return Account::calculateVat($amount, $vat_percent);
    }

    /**
     * Returns highest voucher number
     *
     * @deprecated
     *
     * @return (int) maks vouchernumber
     */
    function getMaxNumber()
    {
        $gateway = new Intraface_modules_accounting_VoucherGateway($this->year);
        return $gateway->getMaxNumber();
    }

    public function getId()
    {
        return $this->id;
    }
}
