<?php
/**
 * @package Intraface_Accounting
 */
class Intraface_modules_accounting_VoucherFileGateway
{
    private $voucher;
    private $what_can_i_belong_to = array(
        0 => '_invalid_',
        1 => 'invoice',
        2 => 'procurement',
        3 => 'file',
        4 => 'vat',
        5 => 'credit_note',
        6 => 'reminder'
    );

    function __construct($voucher)
    {
        $this->voucher = $voucher;
    }

    function findFromId($id)
    {
        require_once dirname(__FILE__) . '/VoucherFile.php';
        return new VoucherFile($this->voucher, $id);
    }

    function getList()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM accounting_voucher_file WHERE active = 1 AND voucher_id = " . $this->voucher->get('id') . " AND intranet_id=" . $this->voucher->year->kernel->intranet->get('id'));
        $i = 0;
        $files = array();
        while ($db->nextRecord()) {
            $files[$i]['id'] = $db->f('id');
            $files[$i]['description'] = $db->f('description');

            switch ($this->what_can_i_belong_to[$db->f('belong_to_key')]) {
                case 'invoice':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Faktura';
                    }
                    $files[$i]['name'] = 'Faktura';
                    $files[$i]['file_uri'] = $this->url('/module/debtor/invoice/list/'.$db->f('belong_to_id').'.pdf');
                break;
                case 'vat':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Momsperiode';
                    }
                    $files[$i]['name'] = 'Momsperiode';
                    // @todo how to fix this?
                    $files[$i]['file_uri'] = $this->url('/module/accounting/vat_view.php', array('id' => $db->f('belong_to_id')));
                break;

                case 'file':
                    $this->voucher->year->kernel->useModule('filemanager');
                    $filehandler = new FileHandler($this->voucher->year->kernel, $db->f('belong_to_id'));
                    $files[$i]['name'] = $filehandler->get('file_name');
                    $files[$i]['description'] = $filehandler->get('file_name');
                    $files[$i]['file_uri'] = $filehandler->get('file_uri');
                break;
                case 'credit_note':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Kreditnota';
                    }
                    $files[$i]['name'] = 'Kreditnota';
                    $files[$i]['file_uri'] = $this->url('/module/debtor/credit_note/' . $db->f('belong_to_id') . '.pdf');
                break;
                case 'reminder':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Rykker';
                    }
                    $files[$i]['name'] = 'Rykker';
                    // @todo fix this
                    $files[$i]['file_uri'] = $this->url('/module/debtor/reminder_pdf.php', array('id' => $db->f('belong_to_id')));
                break;
                case 'procurement':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Indkøb';
                    }
                    $files[$i]['name'] = 'Indkøb';
                    $files[$i]['file_uri'] = $this->url('/module/procurement/'.$db->f('belong_to_id'));
                break;
                default:
                    throw new Exception('VoucherFile::getList: ugyldig belong to');
                break;

            }
            $i++;
        }
        return $files;
    }

    function url($url)
    {
        return PATH_WWW . 'restricted' . $url;
    }
}