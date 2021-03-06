<?php
class Intraface_modules_currency_Controller_ExchangeRate_Update extends k_Component
{
    private $error;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getTranslation()
    {
        return $this->context->getTranslation();
    }

    public function getCurrency()
    {
        return $this->context->getCurrency();
    }

    /**
     * Returns Error object
     *
     * @return object Intraface_Doctrine_ErrorRender
     */
    private function getError()
    {
        if (!$this->error) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getTranslation());
        }

        return $this->error;
    }

    function renderHtml()
    {
        $rate =  new Intraface_modules_currency_Currency_ExchangeRate;
        $types = $rate->getUsedForTypes();
        if (false === ($used_for_key = array_search($this->context->name(), $types))) {
            throw new Exception('Invalid used for '.$this->context->name());
        }

        if ($this->context->name() == 'payment') {
            $this->document->setTitle('Update exchange rate for payments');
        } elseif ($this->context->name() == 'productprice') {
            $this->document->setTitle('Update exchange rate for product prices');
        } else {
            throw new Exception('Invalid context');
        }
        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/exchangerate-add');
        return $this->getError()->view() . $tpl->render($this);
    }

    function postForm()
    {
        if ($this->context->name() == 'payment') {
            $exchangerate = new Intraface_modules_currency_Currency_ExchangeRate_Payment;
        } elseif ($this->context->name() == 'productprice') {
            $exchangerate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        } else {
            throw new Exception('Invalid context');
        }

        $exchangerate->setCurrency($this->getCurrency());
        $exchangerate->setRate(new Ilib_Variable_Float($this->body('rate'), 'da_dk'));

        try {
            $exchangerate->save();
            return new k_SeeOther($this->url('../../../../'));
        } catch (Doctrine_Validator_Exception $e) {
            $this->getError()->attachErrorStack($currency->getErrorStack(), array('rate' => 'rate'));
        }
        return $this->render();
    }
}
