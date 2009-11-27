<?php
class Intraface_modules_debtor_Controller_Payments extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_debtor_Controller_Payment';
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
        $object = $this->getDebtor();
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
}