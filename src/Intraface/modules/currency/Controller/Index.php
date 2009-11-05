<?php
class Intraface_modules_currency_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    public function getTranslation()
    {
        return $this->registry->get('kernel')->getTranslation('currency');
    }

    function t($phrase)
    {
        return $phrase;
    }

    function __($phrase)
    {
        return $phrase;
    }


    function GET()
    {
        //$this->document->options = array($this->url('add') => 'Add new');

        $doctrine = $this->registry->get('doctrine');

        try {
            $gateway = new Intraface_modules_currency_Currency_Gateway($doctrine);
            $currencies = $gateway->findAll();
        } catch (Intraface_Gateway_Exception $e) {
            $currencies = NULL;
        }

        $smarty = new k_Template('Intraface/modules/currency/Controller/tpl/empty-table.tpl.php');

        if ($currencies == NULL) {
            $smarty = new k_Template('Intraface/modules/currency/Controller/tpl/empty-table.tpl.php');

            return $smarty->render($this, array('message' => 'No currencies has been added yet.'));
        }
        $smarty = new k_Template('Intraface/modules/currency/Controller/tpl/currencies.tpl.php');
        return $smarty->render($this, array('currencies' => $currencies));
    }

    function map($name)
    {
        if ($name == 'add') {
            return 'Intraface_modules_currency_Controller_Add';
        }
        return 'Intraface_modules_currency_Controller_Show';

    }
}