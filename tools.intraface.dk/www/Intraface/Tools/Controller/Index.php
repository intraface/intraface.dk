<?php
class Intraface_Tools_Controller_Index extends k_Controller
{
    public $map = array('errorlog' => 'Intraface_Tools_Controller_ErrorLog',
                        'phpinfo' => 'Intraface_Tools_Controller_Phpinfo',
                        'log' => 'Intraface_Tools_Controller_Log',
                        'translation' => 'Translation2_Frontend_Controller_Index');

    function GET()
    {
        return $this->render(dirname(__FILE__) . '/../tpl/index-tpl.php');
    }

}