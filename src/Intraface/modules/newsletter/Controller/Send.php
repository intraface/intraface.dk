<?php
class Intraface_modules_newsletter_Controller_Send extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = new k_Template(dirname(__FILE__) . '/templates/send.tpl.php');
        return $smarty->render($this);
    }

    function DELETE()
    {
        $letter = new Newsletter($list, $_GET['delete']);
        $letter->delete();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getList()
    {
        return $this->context->getList();
    }

    function getLetter()
    {
        return $this->context->getLetter();
    }

    function postForm()
    {
        if ($this->context->getLetter()->queue()) {
            return new k_SeeOther($this->url('../', array('flare' => 'Newsletter has been sent')));
        }
        return $this->render();
    }
}
