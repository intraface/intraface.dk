<?php
class Intraface_modules_debtor_Controller_Payment extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function map($name)
    {
        return 'Intraface_modules_accounting_Controller_State_Payment';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $translation = $this->getKernel()->getTranslation('debtor');
        $object = $this->getModel();
            $payment = $this->getPayment();
            if ($id = $payment->update($_POST)) {
                    if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                        return new k_SeeOther($this->url($id . '/state'));
                    } else {
                        return new k_SeeOther($this->url('../'));
                    }

            }
            return $this->render();
    }

    function getModel()
    {
        return $this->context->getModel();
    }

    function getPayment()
    {
        $object = $this->getModel();
        return $payment = new Payment($object);
    }

    function getType()
    {
        return $this->context->getType();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/payment.tpl.php');
        return $smarty->render($this);
    }

    function t($phrase)
    {
        return $phrase;
    }
}