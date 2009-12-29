<?php
class Intraface_modules_debtor_DebtorGateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Bruges til at lave en menu p� kontakten eller produktet
     *
     * @param string  $type    contact eller product
     * @param integer $type_id id p� contact eller product.
     *
     * @return integer
     */
    public function findCountByContactId($contact_id)
    {
                $sql = "SELECT id
                FROM debtor
                    WHERE intranet_id = " . $this->kernel->intranet->get("id") . "
                        AND contact_id = ".(int)$contact_id."
              AND type='".$this->type_key."'
              AND active = 1";

        $db = new DB_Sql;
        $db->query($sql);
        return $db->numRows();
    }

    function setNewContactId($old_contact_id, $new_contact_id)
    {
        $db = new DB_Sql;
        $db->query('UPDATE debtor SET contact_id = ' . $new_contact_id . ' WHERE contact_id = ' . $old_contact_id);
        return true;
    }
}