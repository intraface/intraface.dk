<?php

class Intraface_Category extends Ilib_Category 
{
    
    
    public function __construct($kernel, $db, $type, $id = NULL)
    {
        $options = array('intranet_id = '.$kernel->intranet->getId());
        parent::__contruct($db, $type, $id, $options);
    }
}

?>