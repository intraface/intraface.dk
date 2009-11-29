<?php
class Intraface_modules_shop_Controller_Edit extends k_Component
{
    private $error;
    protected $doctrine;
    protected $template;

    function __construct(Doctrine_Connection_Common $doctrine, k_TemplateFactory $template)
    {
        $this->doctrine = $doctrine;
        $this->template = $template;
    }

    function getError()
    {
        if ($this->error) {
            return $this->error;
        }
        return ($this->error = new Intraface_Error());
    }

    function isValid()
    {
        if (!$this->body()) {
        	return true;
        }
        $validator = new Intraface_Validator($this->getError());
        $validator->isNumeric($this->body('show_online'), 'show_online skal være et tal');
        // $validator->isString($this->POST['description'], 'description text is not valid');
        $validator->isString($this->body('confirmation_subject'), 'confirmation subject is not valid', '', 'allow_empty');
        $validator->isString($this->body('confirmation'), 'confirmation text is not valid', '', 'allow_empty');
        $validator->isString($this->body('confirmation_greeting'), 'confirmation greeting is not valid', '', 'allow_empty');
        $validator->isString($this->body('terms_of_trade_url'), 'terms of trade is not valid', '', 'allow_empty');
        $validator->isString($this->body('receipt'), 'shop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');

        return !$this->getError()->isError();
    }

    function getModel()
    {
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name());
        return $shop;
    }

    function renderHtml()
    {
        $this->document->setTitle('Edit shop');

        $data = array();

        if (is_numeric($this->context->name())) {
            $post = $this->body();
            if (!empty($post)) {
                $data = $post;
            } else {
                $shop = $this->getModel();
                $data = $shop->toArray();
            }
        } elseif (!$this->isValid()) {
            $data = $this->body();
        } else {
            $data['receipt'] = $this->getKernel()->setting->get('intranet','webshop.webshop_receipt');
        }

        if ($this->getKernel()->intranet->hasModuleAccess('currency')) {
            $this->getKernel()->useModule('currency', true); // true: ignore user access
            $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
            try {
                $currencies = $gateway->findAllWithExchangeRate();
            } catch (Intraface_Gateway_Exception $e) {
                $currencies = array();
            }
        } else {
            $currencies = false;
        }

        $webshop_module = $this->getKernel()->module('shop');
        $settings = $webshop_module->getSetting('show_online');
        $languages = new Intraface_modules_language_Languages;
        $langs = $languages->getChosenAsArray();

        $data = array(
            'data' => $data,
            'settings' => $settings,
            'currencies' => $currencies,
            'languages' => $langs);
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/edit');
        return $this->getError()->view() . $tpl->render($this, $data);
    }

    function postForm()
    {
        if (!$this->isValid()) {
            return $this->render();
        }

        try {
            if (is_numeric($this->context->name())) {
                $shop = $this->getModel();
            } else {
                $shop = new Intraface_modules_shop_Shop;
                $shop->intranet_id = $this->getKernel()->intranet->getId();
            }
            $shop->fromArray($this->body());
            if ($this->body('confirmation_add_contact_url') == 1) {
                $shop->confirmation_add_contact_url = 1;
            } else {
                $shop->confirmation_add_contact_url = 0;
            }
            if ($this->body('payment_link_add') == 1) {
                $shop->payment_link_add = 1;
            } else {
                $shop->payment_link_add = 0;
            }
            if ($this->body('send_confirmation') == 1) {
                $shop->send_confirmation = 1;
            } else {
                $shop->send_confirmation = 0;
            }
            $shop->save();
        } catch (Exception $e) {
            throw $e;
        }

        return new k_SeeOther($this->url('../'));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}