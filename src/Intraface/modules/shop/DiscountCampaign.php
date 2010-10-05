<?php
/**
 * Not finished. Can be removed if costumers no longer interested 6/3 2010 /Sune
 * @author sune
 *
 */
class Intraface_modules_shop_DiscountCampaign extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop_dicount_campaign');
        $this->hasColumn('name', 'string',  255, array('type' => 'string', 'length' => 255, 'notnull' => true));
        $this->hasColumn('voucher_code_prefix', 'string',  255, array('type' => 'string', 'length' => 255, 'notnull' => true));
        // $this->hasColumn('start_date', 'string',  65555);
        // $this->hasColumn('end_date', 'string',  255);
        // $this->hasColumn('validity_period', 'string',  255);
        // $this->hasColumn('show_online', 'integer', 1);
        // $this->hasColumn('show_online', 'integer', 1);

    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');
        
        $this->hasMany('Intraface_modules_shop_DiscountCampaign_Voucher as voucher',
            array('local' => 'id', 'foreign' => 'shop_discount_campaign_id'));
    }

    function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
    
    /**
     * returns Doctrine_Collection of vouchers 
     * 
     * @return object Doctrine_Collection
     */
    public function getVouchers()
    {
        return $this->vouchers;
    }
    
    /**
     * returns Doctrine_Collection of vouchers 
     * 
     * @return object Doctrine_Collection
     */
    public function getVoucher($id = null)
    {
        if ($id != null) {
            return $this->vouchers[$id];
        }
        return $this->vouchers->get(null); // returns empty object
    }
    
    
}
?>