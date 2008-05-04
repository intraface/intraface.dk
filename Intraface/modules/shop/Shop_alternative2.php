<?php

class Intraface_modules_shop_Shop_record extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop');
        $this->hasColumn('name',                    'string',  255);
        $this->hasColumn('description',             'string',  65555);
        $this->hasColumn('identifier',              'string',  255);
        $this->hasColumn('show_online',             'integer',  1);
        $this->hasColumn('confirmation',       'string',  65555);
        $this->hasColumn('receipt', 'string',  65555);
    }
}

class Intraface_modules_shop_Shop
{
    
    public $error;
    private $user;
    
    public function __construct($user, $record) {
        
        $this->error = new Error();
        $this->user = $user;
        $this->record = $record;
        
        
    }
    
    public function validate() {
        
        
        $validator = new Validator($this->error);
        $validator->isNumeric($this->record->show_online, 'show_online skal være et tal');
        $validator->isString($this->record->description, 'confirmation text is not valid');
        $validator->isString($this->record->confirmation, 'confirmation text is not valid');
        $validator->isString($this->record->receipt, 'webshop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');
    }
    
    public function save() {
        
        
        if(!$this->validate()) {
            return false;
        }
        $this->record->intranet_id = $user->getActiveIntranetId();
        
        $this->record->save();
        
    }
}