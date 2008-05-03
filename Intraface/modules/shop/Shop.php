<?php
class Intraface_modules_shop_Shop extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name',        'string',  255);
        $this->hasColumn('description', 'string',  255);
    }
}