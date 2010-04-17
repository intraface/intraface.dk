<?php
class Intraface_modules_shop_Controller_Index extends k_Component
{
    protected $template;
    protected $error;

    function __construct(k_TemplateFactory $template)
    {
         $this->template = $template;
    }

    function map($name)
    {
        /**
         * Not finished. Can be removed if no costumers no longer interested
        if ($name == 'discount-campaigns') {
            return 'Intraface_modules_shop_Controller_DiscountCampaigns';
        } */

        return 'Intraface_modules_shop_Controller_Show';
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
        $validator->isNumeric($this->body('show_online'), 'show_online skal v�re et tal');
        // $validator->isString($this->POST['description'], 'description text is not valid');
        $validator->isString($this->body('confirmation_subject'), 'confirmation subject is not valid', '', 'allow_empty');
        $validator->isString($this->body('confirmation'), 'confirmation text is not valid', '', 'allow_empty');
        $validator->isString($this->body('confirmation_greeting'), 'confirmation greeting is not valid', '', 'allow_empty');
        $validator->isString($this->body('terms_of_trade_url'), 'terms of trade is not valid', '', 'allow_empty');
        $validator->isString($this->body('receipt'), 'shop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');

        return !$this->getError()->isError();
    }

    function renderHtml()
    {
        $this->document->setTitle('Shops');
        $this->document->options = array($this->url(null, array('create')) => 'Create');

        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findByIntranetId($this->getKernel()->intranet->getId());

        if (count($shops) == 0) {
            $tpl = $this->template->create(dirname(__FILE__) . '/tpl/empty-table');
            return $tpl->render($this, array('message' => 'No shops has been created yet.'));
        }

        $data = array('shops' => $shops);
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/shops');
        return $tpl->render($this, $data);
    }

    function renderHtmlCreate()
    {
        $this->document->setTitle('Create shop');

        $data = array();

        if (!$this->isValid()) {
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
            $shop = new Intraface_modules_shop_Shop;
            $shop->intranet_id = $this->getKernel()->intranet->getId();

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

            return new k_SeeOther($this->url($shop->getId()));
        } catch (Exception $e) {
            throw $e;
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
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

    function document()
    {
        return $this->document;
    }
}