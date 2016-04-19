<?php
class Intraface_Tools_Controller_Phpinfo extends k_Component
{
    function renderHtml()
    {
        ob_start();
        phpinfo(); // phpinfo echoes result;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
