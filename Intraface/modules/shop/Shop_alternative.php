<?php
class Intraface_modules_shop_Shop extends Doctrine_Record
{
    
    public $error;
    private $user;
    
    public function __construct($user, $id = 0) {
        
        $this->error = new Error();
        $this->user = $user;
        
        
    }
    
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
    
    public function validate() {
        
        
        $validator = new Validator($this->error);
        $validator->isNumeric($this->show_online, 'show_online skal være et tal');
        $validator->isString($this->description, 'confirmation text is not valid');
        $validator->isString($this->confirmation, 'confirmation text is not valid');
        $validator->isString($this->receipt, 'webshop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');
    }
    
    public function save() {
        
        
        if(!$this->validate()) {
            return false;
        }
        $this->intranet_id = $user->getActiveIntranetId();
        
        parent::save();
        
    }
}