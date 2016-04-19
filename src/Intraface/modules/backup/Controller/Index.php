<?php
class Intraface_modules_backup_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this);
    }

    function postForm()
    {
        if (!empty($_POST['mysql'])) {
            if (!exec('bash /home/intraface/backup/mysql.sh')) {
                die('no success');
            }
        } elseif (!empty($_POST['domain'])) {
            if (!exec('bash /home/intraface/backup/domain.sh')) {
                die('no success');
            }
        }
        return $this->render();
    }
}
