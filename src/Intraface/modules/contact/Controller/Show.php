<?php
class Intraface_modules_contact_Controller_Show extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');

        return new k_SeeOther(PATH_WWW."modules/contact/contact.php?id=".$this->name());
        /*
        $smarty = new k_Template(dirname(__FILE__) . '/templates/show.tpl.php');
        return $smarty->render($this);
		*/
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

    function map($name)
    {
        if ($name == 'merge') {
            return 'Intraface_modules_contact_Controller_Merge';
        }

    }
}