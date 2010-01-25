<?php
class Intraface_Tools_Controller_Phpinfo extends k_Component
{
    function renderHtml()
    {
        ob_start();

        phpinfo(); // phpinfo echoes result;
        $content = ob_end_flush();
        return $content;
    }
}