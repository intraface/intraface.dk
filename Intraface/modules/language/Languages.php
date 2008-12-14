<?php
class Intraface_modules_language_Languages extends Doctrine_Record
{
    private $type;

    public function setTableDefinition()
    {
        $this->setTableName('language');
        $this->hasColumn('type_key', 'integer', 11, array('greaterthan' => 0));
        $this->hasColumn('intranet_id', 'integer', 11, array('greaterthan' => 0));
    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        //$this->actAs('SoftDelete');
    }

    function getChosen()
    {
    	return Doctrine::getTable('Intraface_modules_language_Languages')->findByIntranetId($GLOBALS['intraface_doctrine_intranet_id']);
    }

    function flush()
    {
    	$q = Doctrine_Query::create();
        $rows = $q->delete()
          ->from('Intraface_modules_language_Languages')
          ->where('intranet_id = ?', $GLOBALS['intraface_doctrine_intranet_id'])
          ->execute();
        return true;
    }
}