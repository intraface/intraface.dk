<?php
class Intraface_Tools_Controller_Phpinfo extends k_Controller
{
    function GET()
    {
        return phpinfo();
    }

}