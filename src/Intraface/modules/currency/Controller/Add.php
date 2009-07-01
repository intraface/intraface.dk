<?php
class Intraface_modules_currency_Controller_Add extends k_Controller
{
    private $error;
    
    public function getError()
    {
        if (!$this->error) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->context->getTranslation());
        }
        
        return $this->error;
    }
    
    function GET()
    {
        $this->document->title = 'Add currency';
        
        if (is_numeric($this->context->name)) {
            
        }
        
        $type_gateway = new Intraface_modules_currency_Currency_Type;
        $data['currency_types'] = $type_gateway->getAll(); 

        return $this->getError()->view() . $this->render('Intraface/modules/currency/Controller/tpl/add.tpl.php', $data);
    }

    function POST()
    {
        
        $doctrine = $this->registry->get('doctrine');
        
        
        $type_gateway = new Intraface_modules_currency_Currency_Type;
        $type = $type_gateway->getByIsoCode($this->POST['type_iso_code']);
        
        $currency = new Intraface_modules_currency_Currency;
        $currency->setType($type);
        try {
            $currency->save();
            throw new k_http_Redirect($this->url('../'));
        }
        catch (Doctrine_Validator_Exception $e) {
            $this->getError()->attachErrorStack($currency->getErrorStack(), array('type' => 'currency'));
            return $this->GET();
        }
    }
}