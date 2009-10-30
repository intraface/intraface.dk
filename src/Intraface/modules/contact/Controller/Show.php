<?php
class Intraface_modules_contact_Controller_Show extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function map($name)
    {
        if ($name == 'merge') {
            return 'Intraface_modules_contact_Controller_Merge';
        }

    }

    function renderHtml()
    {
        $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/show.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLists()
    {
        $list = new NewsletterList($this->getKernel());
        return $list->getList();
    }

    function t($phrase)
    {
         return $phrase;
    }
}