<?php

/**
 * Intraface_modules_debtor_DebtorDoctrine
 * 
 * 
 * @property integer $id
 * @property integer $where_from
 * @property integer $where_from_id
 * @property integer $intranet_id
 * @property integer $user_id
 * @property string $identifier_key
 * @property timestamp $date_created
 * @property timestamp $date_changed
 * @property timestamp $date_sent
 * @property timestamp $date_executed
 * @property timestamp $date_cancelled
 * @property date $date_stated
 * @property integer $voucher_id
 * @property integer $currency_id
 * @property integer $currency_product_price_exchange_rate_id
 * @property date $this_date
 * @property date $due_date
 * @property integer $number
 * @property integer $intranet_address_id
 * @property integer $contact_id
 * @property integer $contact_address_id
 * @property integer $contact_person_id
 * @property string $_old_attention_to
 * @property string $description
 * @property integer $status
 * @property integer $type
 * @property integer $round_off
 * @property integer $payment_method
 * @property string $girocode
 * @property string $ip
 * @property integer $active
 * @property string $comment
 * @property string $message
 * @property string $internal_note
 * 
 * @package    Intraface
 * @subpackage Intraface_modules_debtor
 * @author     Sune Jensen sune@intraface.dk
 */
class Intraface_modules_debtor_DebtorDoctrine extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('debtor');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('where_from', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('where_from_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('intranet_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('user_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('identifier_key', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true));
        $this->hasColumn('date_created', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        $this->hasColumn('date_changed', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        $this->hasColumn('date_sent', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        $this->hasColumn('date_executed', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        $this->hasColumn('date_cancelled', 'timestamp', null, array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00', 'notnull' => true));
        $this->hasColumn('date_stated', 'date', null, array('type' => 'date', 'default' => '0000-00-00', 'notnull' => true));
        $this->hasColumn('voucher_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('currency_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
        $this->hasColumn('currency_product_price_exchange_rate_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'notnull' => true));
        // $this->hasColumn('_old_voucher_number', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true));
        $this->hasColumn('this_date', 'date', null, array('type' => 'date', 'default' => '0000-00-00', 'notnull' => true));
        $this->hasColumn('due_date', 'date', null, array('type' => 'date', 'default' => '0000-00-00', 'notnull' => true));
        $this->hasColumn('number', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('intranet_address_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('contact_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('contact_address_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('contact_person_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('_old_attention_to', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true));
        $this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true));
        $this->hasColumn('status', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('_old_status', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('_old_status_date', 'date', null, array('type' => 'date', 'default' => '0000-00-00', 'notnull' => true));
        // $this->hasColumn('_old_is_credited', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('_old_locked', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
        $this->hasColumn('type', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('round_off', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('payment_method', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('girocode', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true));
        $this->hasColumn('ip', 'string', 255, array('type' => 'string', 'length' => 255, 'default' => '', 'notnull' => true));
        // $this->hasColumn('_old_is_sent', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('_old_payed', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        // $this->hasColumn('_old_payed_date', 'date', null, array('type' => 'date', 'default' => '0000-00-00', 'notnull' => true));
        // $this->hasColumn('_old_is_sent_date', 'date', null, array('type' => 'date', 'default' => '0000-00-00', 'notnull' => true));
        $this->hasColumn('active', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '1', 'notnull' => true));
        $this->hasColumn('comment', 'string', null, array('type' => 'string', 'notnull' => true));
        $this->hasColumn('message', 'string', null, array('type' => 'string', 'notnull' => true));
        $this->hasColumn('internal_note', 'string', null, array('type' => 'string', 'notnull' => true));
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        
        $this->hasMany('Intraface_modules_debtor_Debtor_Item as item',
            array('local' => 'id', 'foreign' => 'debtor_id'));
            
        /* $this->hasMany('Intraface_modules_product_Variation as variation', 
            array('local' => 'id', 'foreign' => 'product_id'));  */
    }
    
    
    /**
     * 
     * @return unknown_type
     */
    public function save()
    {
        throw new Exception('Not yet implemented');
    }
    
    public function delete()
    {
        throw new Exception('Not yet implemented'); // remember soft delete
    }
    
    /**
     * Returns due date
     * @return object Ilib_Variable_String_Date with due date
     */
    public function getDueDate()
    {
        return new Ilib_Variable_String_Date($this->due_date, 'iso');
    }
    
    /**
     * returns possible status types
     *
     * @return array possible status types
     */
    private function getStatusTypes()
    {
        return array(
            0 => 'created',
            1 => 'sent',
            2 => 'executed',
            3 => 'cancelled'
        );
    }
    
    /**
     * Returns status
     * 
     * @return string status
     */
    public function getStatus()
    {
        $types = $this->getStatusTypes();
        return $types[$this->status];
    }
    
    /**
     * Returns number
     * @return integer number
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * Returns description
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Returns debtor (quotation, order, invoice) date
     * @return object Ilib_Variable_String_Date
     */
    public function getDebtorDate()
    {
        return new Ilib_Variable_String_Date($this->this_date, 'iso');
    }
    
    /**
     * returns date sent
     * @return object Ilib_Variable_String_Date
     */
    public function getDateSent()
    {
        return new Ilib_Variable_String_Date($this->date_sent, 'iso');
    }
    
    /**
     * returns Doctrine_Collection of items 
     * @return object Doctrine_Collection
     */
    private function getItems()
    {
        return $this->item;
    }
    
    /**
     * Returns array with item objects
     * @return array
     */
    public function getItemsWithVat()
    {
        $items = $this->getItems();
        foreach ($items AS $item) {
            if ($item->getProduct()->getDetails()->getVatPercent()->getAsIso() == 0) {
                $items->remove($item->getId());
            }
        }
        return $items;
    }
    
    /**
     * Returns items without vat
     * @return Doctrine_Collection
     */
    public function getItemsWithoutVat()
    {
        
    }
    
    /**
     * Returns vat for all items with vat
     * @return Ilib_Float_Variable
     */
    public function getVat()
    {
        
    }
    
    /**
     * Returns the total including vat for the debtor
     * @return Ilib_Variable_Float with total
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->getItems() AS $item) {
            $total += $item->getAmount()->getAsIso();
        }
        
        return new Ilib_Variable_Float($total, 'iso');
    }

}