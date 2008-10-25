<?php
/**
 * @package Intraface_Accounting
 */
class VoucherFile
{
    public $id;
    public $voucher;
    public $what_can_i_belong_to = array(
        0 => '_invalid_',
        1 => 'invoice',
        2 => 'procurement',
        3 => 'file',
        4 => 'vat',
        5 => 'credit_note',
        6 => 'reminder'
    );
    public $error;

    function __construct($voucher, $id=0)
    {
        if (!is_object($voucher)) {
            trigger_error('VoucherFile:: Voucher ikke gyldig', E_USER_ERROR);
        }
        $this->voucher = $voucher;
        $this->id      = (int) $id;
        $this->error   = new Intraface_Error;
    }

    function validate($var)
    {
        $validator = new Intraface_Validator($this->error);
        if (!empty($var['description'])) {
            $validator->isString($var['description'], 'Beskrivelsen ulovlig', '', 'allow_empty');
        }
        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    function save($var)
    {
        $var = safeToDb($var);

        if (!$this->validate($var)) {
            return 0;
        }

        $db = new DB_Sql();
        $sql = "SELECT id FROM accounting_voucher_file
            WHERE intranet_id = " . $this->voucher->year->kernel->intranet->get('id') . "
                AND belong_to_key = ".array_search($var['belong_to'], $this->what_can_i_belong_to)."
                AND belong_to_id = ".$var['belong_to_id']."
                AND voucher_id = ".$this->voucher->getId() . " AND active = 1";
        $db->query($sql);
        if ($db->nextRecord()) {
            // hvis filen allerede er tilknyttet lader vi som om alt gik godt, og vi siger go
            // dette skal naturligvis laves lidt anderledes, hvis vi skal have en description med
            return 1;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;

        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = " , date_created = NOW()";
        }

        if (empty($var['description'])) $var['description'] = '';

        $sql = $sql_type . " accounting_voucher_file SET
            date_updated = NOW(),
            intranet_id = ".$this->voucher->year->kernel->intranet->get('id').",
            voucher_id = ".$this->voucher->get('id').",
            belong_to_key = ".array_search($var['belong_to'], $this->what_can_i_belong_to).",
            belong_to_id = ".$var['belong_to_id'].",
            description = '".$var['description']."'
            " . $sql_end;
        $db->query($sql);
        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
        return $this->id;
    }

    function delete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE accounting_voucher_file SET active = 0 WHERE id = " . $this->id);
        return 1;
    }

    function undelete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE accounting_voucher_file SET active = 1 WHERE id = " . $this->id);
        return 1;
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
                    $files[$i]['file_uri'] = url('/modules/debtor/pdf.php', array('id' => $db->f('belong_to_id')));
                break;
                case 'vat':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Momsperiode';
                    }
                    $files[$i]['name'] = 'Momsperiode';
                    $files[$i]['file_uri'] = url('/modules/accounting/vat_view.php', array('id' => $db->f('belong_to_id')));
                break;

                case 'file':
                    $this->voucher->year->kernel->useShared('filehandler');
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
                    $files[$i]['file_uri'] = url('/modules/debtor/pdf.php', array('id' => $db->f('belong_to_id')));
                break;
                case 'reminder':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Rykker';
                    }
                    $files[$i]['name'] = 'Rykker';
                    $files[$i]['file_uri'] = url('/modules/debtor/reminder_pdf.php', array('id' => $db->f('belong_to_id')));
                break;
                case 'procurement':
                    if (empty($files[$i]['description'])) {
                        $files[$i]['description'] = 'Indkøb';
                    }
                    $files[$i]['name'] = 'Indkøb';
                    $files[$i]['file_uri'] = url('/modules/procurement/view.php', array('id' => $db->f('belong_to_id')));
                break;
                default:
                    trigger_error('VoucherFile::getList: ugyldig belong to');
                break;

            }
            $i++;
        }
        return $files;
    }
}