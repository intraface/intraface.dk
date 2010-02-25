<?php
class Intraface_modules_currency_Controller_Add extends k_Component
{
    protected $error;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Add currency');

        if (is_numeric($this->context->name())) {

        }

        $type_gateway = new Intraface_modules_currency_Currency_Type;
        $data['currency_types'] = $type_gateway->getAll();

        $tpl = $this->template->create('Intraface/modules/currency/Controller/tpl/add');

        return $this->getError()->view() . $tpl->render($this, $data);
    }

    function postForm()
    {
        $type_gateway = new Intraface_modules_currency_Currency_Type;
        $type = $type_gateway->getByIsoCode($this->body('type_iso_code'));

        $currency = new Intraface_modules_currency_Currency;
        $currency->setType($type);
        try {
            $currency->save();
            return new k_SeeOther($this->url('../'));
        } catch (Doctrine_Validator_Exception $e) {
            $this->getError()->attachErrorStack($currency->getErrorStack(), array('type' => 'currency'));
        }
        return $this->render();
    }


    public function getError()
    {
        if (!$this->error) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->context->getTranslation());
        }

        return $this->error;
    }
}