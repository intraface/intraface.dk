<?php
class Intraface_Tools_Controller_Log extends k_Component
{
    protected $template;
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2, k_TemplateFactory $template)
    {
        $this->mdb2 = $mdb2;
        $this->template = $template;
    }

    function renderHtml()
    {
        $res = $this->mdb2->query("SELECT logtime, ident, message FROM log_table ORDER BY logtime DESC");
        $tpl = $this->template->create('Intraface/Tools/templates/log');
        return $tpl->render($this, array('res' => $res));
    }
}