<?php
class Intraface_modules_shop_Controller_Show extends k_Component
{
    protected $mdb2;
    protected $template;
    protected $error;
    protected $doctrine;

    function __construct(MDB2_Driver_Common $db, k_TemplateFactory $template, Doctrine_Connection_Common $doctrine)
    {
        $this->mdb2 = $db;
        $this->template = $template;
        $this->doctrine = $doctrine;
    }

    function map($name)
    {
        if ($name == 'basketevaluation') {
            return 'Intraface_modules_shop_Controller_BasketEvaluation_Index';
        } elseif ($name == 'featuredproducts') {
            return 'Intraface_modules_shop_Controller_FeaturedProducts';
        } elseif ($name == 'categories') {
            return 'Intraface_modules_shop_Controller_Categories';
        } elseif ($name == 'paymentmethods') {
            return 'Intraface_modules_shop_Controller_PaymentMethods_Index';
        }
    }

    function renderHtml()
    {
        $shop = $this->getShop();

        $this->document->setTitle($shop->name);

        $this->document->options = array($this->url('../') => 'Close',
                                         $this->url(null, array('edit')) => 'Edit',
                                         $this->url('featuredproducts') => 'Choose featured products',
                                         $this->url('categories') => 'Product categories',
                                         $this->url('basketevaluation') => 'Basket evaluation',
                                         $this->url('paymentmethods') => 'Payment methods');

        $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $shop);
        $evaluations = $basketevaluation->getList();

        $data = array('shop' => $shop, 'evaluations' => $evaluations);

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/show');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $this->document->setTitle('Edit shop');

        $data = array();


            $post = $this->body();
            if (!empty($post)) {
                $data = $post;
            } else {
                $shop = $this->getModel();
                $data = $shop->toArray();
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
            $shop = $this->getModel();

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

    function getShopId()
    {
        return $this->name();
    }

    function getShop()
    {
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->getShopId());
        return $shop;
    }

    function getModel()
    {
        return $this->getShop();
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
}