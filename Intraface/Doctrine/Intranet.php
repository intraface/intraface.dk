<?php
class Intraface_Doctrine_Intranet
{
    private $id;
    
    public function __construct($id) 
    {
        $this->id = $id;
    }
    
    public static function singleton($id = NULL)
    {
        if($id != NULL) {
            $GLOBALS['intraface_doctrine_intranet_id'] = intval($id);    
        }
        if(empty($GLOBALS['intraface_doctrine_intranet_id'])) {
            throw new Exception('An intranet id was not set!');
        }
        return new Intraface_Doctrine_Intranet($GLOBALS['intraface_doctrine_intranet_id']);
    }
    
    public function getId()
    {
        return $this->id;
    }   
}