<?php
class Intraface_modules_debtor_Controller_Payment extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function postForm()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $translation = $this->getKernel()->getTranslation('debtor');
            $payment = $this->getPayment();
            if ($payment->update($_POST)) {
                    if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                        header('location: state_payment.php?for=invoice&id=' . intval($object->get("id")).'&payment_id='.$payment->get('id'));
                        exit;
                    } else {
                        return new k_SeeOther($this->url('../'));
                    }

            }
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