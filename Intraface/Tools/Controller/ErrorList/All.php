<?php

class Intraface_Tools_Controller_ErrorList_All extends Intraface_Tools_Controller_ErrorList
{
    
    public function getErrorList() 
    {
        return $this->registry->get('errorlist')->getAll();
    }
    
}