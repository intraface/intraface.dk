<?php
class Intraface_modules_shop_Shop extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop');
        $this->hasColumn('name',         'string',  255);
        $this->hasColumn('description',  'string',  65555);
        $this->hasColumn('identifier',   'string',  255);
        $this->hasColumn('show_online',  'integer',  1);
        $this->hasColumn('confirmation', 'string',  65555);
        $this->hasColumn('receipt',      'string',  65555);
        $this->hasColumn('intranet_id',  'string',  65555);

    }

    function getId()
    {
        return $this->id;
    }
}