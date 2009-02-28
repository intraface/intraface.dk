<?php

class Intraface_Tools_Controller_ErrorList_Unique extends Intraface_Tools_Controller_ErrorList
{
    
    public function getErrorList() 
    {
        return $this->registry->get('errorlist')->getUnique();
    }
    
}
