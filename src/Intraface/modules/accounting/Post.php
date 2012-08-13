<?php
/**
 * Handles posts
 *
 * It is not allowed to change existing accounting posts.
 *
 * @package Intraface_Accounting
 * @author Lars Olesen
 * @since 1.0
 * @version 1.0
 */

class Post extends Intraface_Standard
{
    public $id; // integer
    public $voucher; // object
    public $error;
    public $value;

    /**
     * Init
     *
     * @param $year_object (object)
     * @param $post_id (int) refererer til en enkelt post
     *
     * @return void
     */
    function __construct($voucher, $post_id = 0)
    {
        $this->voucher = $voucher;
        $this->id      = (int)$post_id;
        $this->error   = new Intraface_Error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    public function factory($year, $post_id)
    {
        return Intraface_modules_accounting_PostGateway::getFromYearAndPostId($year, $post_id);
    }

    private function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT *, DATE_FORMAT(date, '%d-%m-%Y') AS date_dk FROM accounting_post WHERE id = " . $this->id . " AND intranet_id=" .$this->voucher->year->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return 0;
        }

        $this->value['id'] = $db->f('id');
        $this->value['text'] = $db->f('text');
        $this->value['date'] = $db->f('date');
        $this->value['date_dk'] = $db->f('date_dk');
        $this->value['debet'] = $db->f('debet');
        $this->value['credit'] = $db->f('credit');
        $this->value['account_id'] = $db->f('account_id');
        $this->value['voucher_id'] = $db->f('voucher_id');
        $this->value['stated'] = $db->f('stated');

        $account_gateway = new Intraface_modules_accounting_AccountGateway($this->voucher->year);
        $account = $account_gateway->findFromId($this->value['account_id']);
        //$account = new Account($this->voucher->year, $this->value['account_id']);
        $this->value['account_number'] = $account->get('number');
        $this->value['account_name'] = $account->get('name');

        $voucher_gateway = new Intraface_modules_accounting_VoucherGateway($this->voucher->year);
        $voucher = $voucher_gateway->findFromId($this->value['voucher_id']);

        //$voucher = new Voucher($this->voucher->year, $this->value['voucher_id']);
        $this->value['voucher_number'] = $voucher->get('number');

        $this->value['reference'] = $this->voucher->get('reference');

        return 1;
    }

    private function validate($date, $account_id, $text, $debet, $credit)
    {
        $validator = new Intraface_Validator($this->error);

        // @todo Strange that this does not validate - isDate does not accept: 2006-07-15
        // $validator->isDate($date, 'Datoen ' . $date .  ' er ikke en gyldig dato');
        $validator->isNumeric($account_id, 'Kontoen er ikke et tal');
        $validator->isString($text, 'Teksten er ikke gyldig');

        // Validerer 29.5 forkert
        //$validator->isDouble($debet, 'Debetbeløbet '.$debet.' er ikke gyldigt');
        //$validator->isDouble($credit, 'Kreditbeløbet '.$credit .' er ikke gyldigt');

        if ($this->error->isError()) {
            return 0;
        }

        return 1;
    }

    /**
     * States the posts
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
    public function save($date, $account_id, $text, $debet, $credit, $skip_draft = false)
    {
        $debet = (float)$debet;
        $credit = (float)$credit;

        if ($this->get('stated') == 1) {
            $this->error->set('Du kan ikke opdatere en bogf�rt post');
            return false;
        }

        if (!$this->validate($date, $account_id, $text, $debet, $credit)) {
            return false;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE";
            $sql_end = " WHERE id = " . $this->id;

        } else {
            $sql_type = "INSERT INTO";
            $sql_end = "";
        }

        $db = new DB_Sql;
        $sql = $sql_type . " accounting_post
                 SET
                    voucher_id = '".$this->voucher->get('id')."',
                    intranet_id = ".$this->
                        voucher->
                            year->
                                kernel->
                                    intranet->
                                        get('id').",
                    user_id = ".$this->voucher->year->kernel->user->get('id').",
                    year_id = '".$this->voucher->year->get('id')."',
                    date = '".$date."',
                    account_id = '".(int)$account_id."',
                    text = '".(string)$text."',
                    debet = '".$debet."',
                    credit = '".$credit."'"  . $sql_end;
        $db->query($sql);

        $this->id = $db->insertedId();

        $this->load();

        if ($skip_draft) {
            $this->setStated();
        }
        return true;
    }

    function getList($type = 'stated')
    {
        $gateway = new Intraface_modules_accounting_PostGateway($this->voucher);
        return $gateway->getList($type);
    }

    public function setStated()
    {
        if ($this->id == 0) {
            $this->error->set('Kan ikke sætte stated, når id = o');
            return false;
        }

        $db = new DB_Sql;
        $db->query("UPDATE accounting_post SET stated = 1 WHERE id = " . $this->id . " AND intranet_id =" .$this->voucher->year->kernel->intranet->get('id'));

        return true;
    }

    public function delete()
    {
        if ($this->id == 0 OR $this->get('stated') == 1) {
            return false;
        }

        $db = new DB_Sql;
        $db->query("DELETE FROM accounting_post WHERE id = " . $this->id);
        return true;

    }

    function getId()
    {
    	return $this->id;
    }
}
