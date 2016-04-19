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
class Intraface_modules_accounting_PostGateway
{
    /**
     * @var object
     */
    public $voucher;

    /**
     * Constructor
     *
     * @param object $voucher
     *
     * @return void
     */
    function __construct($voucher)
    {
        $this->voucher = $voucher;
    }

    function findFromId($id)
    {
        return new Post($this->voucher, $id);
    }

    public static function getFromYearAndPostId($year, $post_id)
    {
        $post_id = (int)$post_id;
        $db = new DB_Sql;
        $db->query("SELECT voucher_id FROM accounting_post WHERE id = " . $post_id . " AND year_id = " . $year->get('id') . " AND intranet_id=" . $year->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return new Post(new Voucher($year));
        }
        $post =  new Post(new Voucher($year, $db->f('voucher_id')), (int)$post_id);
        return $post;

    }

    public function getList($type = 'stated')
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
}
