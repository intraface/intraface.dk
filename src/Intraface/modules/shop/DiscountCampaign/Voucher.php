<?php
/**
 * Not finished. Can be removed if costumers no longer interested 6/3 2010 /Sune
 * @author sune
 *
 */
class Intraface_modules_shop_DiscountCampaign_Voucher extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop_dicount_campaign_voucher');
        $this->hasColumn('shop_discount_campaign_id', 'integer', 4, array('type' => 'integer', 'length' => 4, 'default' => '0', 'notnull' => true));
        $this->hasColumn('code', 'string', 255, array('type' => 'string', 'length' => 255, 'notnull' => true));
        $this->hasColumn('quantity', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => '0', 'notnull' => true));
        $this->hasColumn('date_created', 'timestamp', null, array('type' => 'timestamp', 'default' => new Doctrine_Expression('NOW()'), 'notnull' => true));
        $this->hasColumn('date_expiry', 'timestamp', null, array('type' => 'timestamp', 'notnull' => true));
        $this->hasColumn('used_on_debtor_id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => '0', 'notnull' => true));
        $this->hasColumn('created_from_debtor_id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => '0', 'notnull' => true));

        // $this->hasColumn('end_date', 'string',  255);
        // $this->hasColumn('validity_period', 'string',  255);
        // $this->hasColumn('show_online', 'integer', 1);
        // $this->hasColumn('show_online', 'integer', 1);
    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');

        $this->hasOne(
            'Intraface_modules_shop_DiscountCampaign as campaign',
            array('local' => 'shop_discount_campaign_id', 'foreign' => 'id')
        );
    }

    function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
