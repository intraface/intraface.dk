<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Edit extends k_Controller
{
    function getShop()
    {
        return $this->context->getShop();
    }
    
    function GET()
    {
        if (is_numeric($this->context->name)) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), (int)$this->context->name);
            $value = $basketevaluation->get();
            $this->document->title = 'Edit basket evaluation';
        } else {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop());
            $value = array();
            $this->document->title = 'Create new basket evaluation';
        }
        $settings = $basketevaluation->get('settings');
    
        $data = array('basketevaluation' => $basketevaluation, 'value' => $value, 'settings' => $settings);

        return $this->render('Intraface/modules/shop/Controller/tpl/evaluation.tpl.php', $data);

    }

    function POST()
    {
        
        if (is_numeric($this->context->name)) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop(), (int)$this->context->name);
        } else {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->registry->get('db'), $this->registry->get('intranet'), $this->getShop());
        }

        if (!$basketevaluation->save($this->POST->getArrayCopy())) {
            throw new Exception('Could not save values');
        }
        
        if (is_numeric($this->context->name)) {
            throw new k_http_Redirect($this->url('../../'));
        } else {
            throw new k_http_Redirect($this->url('../'));
        }
    }
}