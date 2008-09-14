<?php
class Model_Product extends Doctrine_Record
{
    public function setTableDefinition ()
    {
        $this->hasColumn('name', 'string', 30);
        $this->hasColumn('description', 'string', 65555);
        $this->hasColumn('price', 'integer', 20);
    }
    public function setUp ()
    {
        $this->actAs('Versionable');
        $this->actAs('I18n', array('fields' => array('description')));
    }
}