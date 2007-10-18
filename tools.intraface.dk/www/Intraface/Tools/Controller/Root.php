<?php

class Intraface_Tools_Controller_Root extends k_Dispatcher
{
    public $map = array('tools' => 'Intraface_Tools_Controller_Index');

    function execute()
    {
        return $this->forward('tools');
    }
}