<?php

/**
 * Håndterer poster i kladden
 *
 * Det er lovmæssigt bestemt, at man ikke må lave om i selve
 * de bogførte poster, så der skal ikke være metoder til at ændre eller
 * slette i bogføringen.
 *
 * @package Intraface_Accounting
 * @author Lars Olesen
 * @since 1.0
 * @version 1.0
 */

class Post extends Standard
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
     */
    function __construct($voucher, $post_id = 0)
    {

        if(!is_object($voucher) OR strtolower(get_class($voucher)) != 'voucher') {
            trigger_error('Klassen Post kræver objektet Voucher', E_USER_ERROR);
            exit;
        }
        $this->voucher = $voucher;
        $this->id = (int)$post_id;
        $this->error = new Error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    function factory($year, $post_id)
    {
        $post_id = (int)$post_id;
        $db = new Db_sql;
        $db->query("SELECT voucher_id FROM accounting_post WHERE id = " . $post_id . " AND year_id = " . $year->get('id') . " AND intranet_id=" . $year->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return new Post(new Voucher($year));
        }
        $post =  new Post(new Voucher($year, $db->f('voucher_id')), (int)$post_id);
        return $post;

    }

    function load()
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

        $account = new Account($this->voucher->year, $this->value['account_id']);
        $this->value['account_number'] = $account->get('number');
        $this->value['account_name'] = $account->get('name');

        $voucher = new Voucher($this->voucher->year, $this->value['voucher_id']);
        $this->value['voucher_number'] = $voucher->get('number');

        $this->value['reference'] = $this->voucher->get('reference');

        return 1;
    }

    function validate($date, $account_id, $text, $debet, $credit)
    {
        $validator = new Validator($this->error);

        // Mærkeligt at denne ikke validerer korrekt - isDate accepterer ikke: 2006-07-15
        // $validator->isDate($date, 'Datoen ' . $date .  ' er ikke en gyldig dato');
        $validator->isNumeric($account_id, 'Kontoen er ikke et tal');
        $validator->isString($text, 'Teksten er ikke gyldig');

        // Validerer 29.5 forkert
        //$validator->isDouble($debet, 'Debetbeløbet '.$debet.' er ikke gyldigt');
        //$validator->isDouble($credit, 'Kreditbeløbet '.$credit .' er ikke gyldigt');

        if ($this->error->isError()) {
            // this must be wrong!
            // echo $this->error->view();
            return 0;
        }

        return 1;
    }

    /**
     * Private: Bogfører selve posterne
     *
     * @param $year_id (int)
     * @param $date (string)
     * @param $voucher_number (string)
     * @param $text (string)
     * @param $account_id (int)
     * @param $debet (float)
     * @param $credit (float)
     *
     * @return boolean
     */
    public function save($date, $account_id, $text, $debet, $credit, $skip_draft = false)
    {
        $debet = (float)$debet;
        $credit = (float)$credit;

        if ($this->get('stated') == 1) {
            $this->error->set('Du kan ikke opdatere en bogført post');
            return 0;
        }

        if (!$this->validate($date, $account_id, $text, $debet, $credit)) {
            return 0;
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
                    intranet_id = ".$this->voucher->year->kernel->intranet->get('id').",
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
        return 1;
    }

    function getList($type = 'stated')
    {
        $db = new DB_Sql;
        $sql = "SELECT voucher.reference, post.id, post.text, post.voucher_id, post.date, post.account_id, post.debet, post.credit, post.stated, DATE_FORMAT(post.date, '%d-%m-%Y') AS date_dk FROM accounting_post post INNER JOIN accounting_voucher voucher ON post.voucher_id = voucher.id WHERE post.year_id = " . $this->voucher->year->get('id') . " AND post.intranet_id = " . $this->voucher->year->kernel->intranet->get('id');
        if ($type == 'stated') {
            $sql .= " AND post.stated = 1";
        } elseif ($type == 'draft') {
            $sql .= " AND post.stated = 0";
        }
        $db->query($sql . " ORDER BY post.voucher_id DESC, post.id DESC");

        $i = 0;
        $this->value['list_saldo'] = 0;
        $list = array();
        while ($db->nextRecord()) {
            $post = new Post($this->voucher, $db->f('id'));
            $list[$i]['id'] = $db->f('id');
            $list[$i]['text'] = $db->f('text');
            $list[$i]['voucher_id'] = $db->f('voucher_id');
            $list[$i]['date'] = $db->f('date');
            $list[$i]['reference'] = $db->f('reference');
            $list[$i]['date_dk'] = $db->f('date_dk');
            $list[$i]['voucher_number'] = $post->get('voucher_number');
            $list[$i]['account_id'] = $db->f('account_id');
            $list[$i]['account_number'] = $post->get('account_number');
            $list[$i]['account_name'] = $post->get('account_name');
            $list[$i]['debet'] = $db->f('debet');
            $list[$i]['credit'] = $db->f('credit');
            $list[$i]['stated'] = $db->f('stated');
            $this->value['list_saldo'] += $list[$i]['debet'] - $list[$i]['credit'];
            $i++;
        }
        return $list;
    }

    function setStated()
    {
        if ($this->id == 0) {
            $this->error->set('Kan ikke sætte stated, når id = o');
            return false;
        }

        $db = new DB_Sql;
        $db->query("UPDATE accounting_post SET stated = 1 WHERE id = " . $this->id . " AND intranet_id =" .$this->voucher->year->kernel->intranet->get('id'));

        return true;
    }

    function delete()
    {
        if ($this->id == 0 OR $this->get('stated') == 1) {
            return false;
        }

        $db = new DB_Sql;
        $db->query("DELETE FROM accounting_post WHERE id = " . $this->id);
        return true;

    }
}