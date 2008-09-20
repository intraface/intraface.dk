<?php
class Intraface_modules_currency_Controller_Index extends k_Controller
{
    
    public function getTranslation() 
    {
        return $this->registry->get('kernel')->getTranslation('currency');
    }
    
    
    function GET()
    {
        $this->document->title = $this->__('Currency');
        $this->document->options = array($this->url('add') => 'Add new');

        $doctrine = $this->registry->get('doctrine');
        
        try {
            $gateway = new Intraface_modules_currency_Currency_Gateway($doctrine);
            $currencies = $gateway->findAll();
        }
        catch (Intraface_Gateway_Exception $e) {
            $currencies = NULL;
        }
        
        if ($currencies == NULL) {
            return $this->render('Intraface/modules/currency/Controller/tpl/empty-table.tpl.php', array('message' => 'No currencies has been added yet.')); 
        }
        
        return $this->render('Intraface/modules/currency/Controller/tpl/currencies.tpl.php', array('currencies' => $currencies));
    }

    function forward($name)
    {
        if ($name == 'add') {
            $next = new Intraface_modules_currency_Controller_Add($this, $name);
            return $next->handleRequest();
        }
        $next = new Intraface_modules_currency_Controller_Show($this, $name);
        return $next->handleRequest();

    }
}