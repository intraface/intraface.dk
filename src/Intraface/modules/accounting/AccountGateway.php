<?php
class Intraface_modules_accounting_AccountGateway
{
    private $year;
    private $types = array(
        1 => 'headline',
        2 => 'operating', // drift
        3 => 'balance, asset', // aktiv
        4 => 'balance, liability', // passiv
        5 => 'sum'
    );

    private $use = array(
        1 => 'none',
        2 => 'income',
        3 => 'expenses',
        4 => 'finance'
    );

    private $vat = array(
        0 => 'none',
        1 => 'in',
        2 => 'out'
    );


    function __construct($year)
    {
        $this->year = $year;
    }

   /**
     * Denne funktion bruges bl.a. under bogf�ringen, s� man bare taster kontonummer
     * og s� s�ttes den rigtige konto.
     *
     * @param integer $account_number
     *
     * @return object
     */
    public function findFromNumber($account_number)
    {
        $account_number = (int)$account_number;

        if ($this->year->get('id') == 0) {
            return 0;
        }

        $sql = "SELECT id FROM accounting_account
            WHERE number = '".$account_number."'
                AND intranet_id = ".$this->year->kernel->intranet->get('id')."
                AND year_id = ".$this->year->get('id')." AND active = 1
            LIMIT 1";

        $db = new Db_Sql;
        $db->query($sql);

        if (!$db->nextRecord()) {
            return new Account($this->year);
        }

        return new Account($this->year, $db->f('id'));

    }

    /**
     * @deprecated
     * @param $id
     * @return unknown_type
     */
    function findFromId($id)
    {
        return $this->findById($id);
    }

    function findById($id)
    {
        require_once dirname(__FILE__) . '/Account.php';
        return new Account($this->year, $id);
    }

    function findByType($type, $saldo = false)
    {
        return $this->getList($type, $saldo);
    }

    function getAll()
    {
        return $this->getList();
    }

    /**
     * Returns collection with all accounts
     *
     * @param string  $type  Typen af konto, kig i Account::type;
     * @param boolean $saldo Whether to return the saldo
     *
     * @return array
     */
    public function getList($type = '', $saldo = false)
    {
        $type = safeToDb($type);
        $type_sql = '';

        //if ($this->year->get('id') == 0 || $this->id == 0) {
        if ($this->year->get('id') == 0) {
            //$this->value['id'] = 0;
            //$this->id = 0;
            return array();
        }

        $db = new DB_Sql;
        if (!empty($type)) {
            switch ($type) {
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

    function anyAccounts()
    {
        $db = new DB_Sql;
        $sql = "SELECT id
            FROM accounting_account
            WHERE intranet_id = " . $this->year->kernel->intranet->get("id") . " AND year_id = ".$this->year->get('id')." AND active = 1";
        $db->query($sql);

        return $db->numRows();
    }
}
