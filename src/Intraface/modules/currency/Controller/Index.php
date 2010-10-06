<?php
class Intraface_modules_currency_Controller_Index extends k_Component
{
    protected $template;
    protected $doctrine;

    function __construct(Doctrine_Connection_Common $doctrine, k_TemplateFactory $template)
    {
        $this->doctrine = $doctrine;
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'add') {
            return 'Intraface_modules_currency_Controller_Add';
        }
        return 'Intraface_modules_currency_Controller_Show';

    }

    function renderHtml()
    {
        $this->document->options = array($this->url('add') => 'Add new');

        try {
            $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
            $currencies = $gateway->findAll();
        } catch (Intraface_Gateway_Exception $e) {
            $currencies = NULL;
        }

        $smarty = $this->template->create('Intraface/modules/currency/Controller/tpl/empty-table');

        if ($currencies == NULL) {
            $smarty = $this->template->create('Intraface/modules/currency/Controller/tpl/empty-table');

            return $smarty->render($this, array('message' => 'No currencies has been added yet.'));
        }
        $tpl = $this->template->create('Intraface/modules/currency/Controller/tpl/currencies');
        return $tpl->render($this, array('currencies' => $currencies));
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/content');
        return $tpl->render($this, array('content' => $content));
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    public function getTranslation()
    {
        return $this->context->getKernel()->getTranslation('currency');
    }
}