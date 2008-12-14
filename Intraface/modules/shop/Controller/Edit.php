<?php
class Intraface_modules_shop_Controller_Edit extends k_Controller
{
    private $error;

    function getError()
    {
        if ($this->error) {
            return $this->error;
        }
        return ($this->error = new Intraface_Error());
    }

    function isValid()
    {
        if (!$this->POST->getArrayCopy()) {
        	return true;
        }
        $validator = new Intraface_Validator($this->getError());
        $validator->isNumeric($this->POST['show_online'], 'show_online skal være et tal');
        // $validator->isString($this->POST['description'], 'description text is not valid');
        $validator->isString($this->POST['confirmation_subject'], 'confirmation subject is not valid', '', 'allow_empty');
        $validator->isString($this->POST['confirmation'], 'confirmation text is not valid', '', 'allow_empty');
        $validator->isString($this->POST['confirmation_greeting'], 'confirmation greeting is not valid', '', 'allow_empty');
        $validator->isString($this->POST['terms_of_trade_url'], 'terms of trade is not valid', '', 'allow_empty');
        $validator->isString($this->POST['receipt'], 'shop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');

        return !$this->getError()->isError();
    }

    function getModel()
    {
        $doctrine = $this->registry->get('doctrine');
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name);
        return $shop;
    }

    function GET()
    {
        $this->document->title = 'Edit shop';

        $data = array();

        if (is_numeric($this->context->name)) {
            $post = $this->POST->getArrayCopy();
            if (!empty($post)) {
                $data = $post;
            } else {
                $shop = $this->getModel();
                $data = $shop->toArray();
            }
        } elseif (!$this->isValid()) {
            $data = $this->POST->getArrayCopy();
        } else {
            $data['receipt'] = $this->registry->get('kernel')->setting->get('intranet','webshop.webshop_receipt');
        }

        if ($this->registry->get('kernel')->intranet->hasModuleAccess('currency')) {
            $this->registry->get('kernel')->useModule('currency', true); // true: ignore user access
            $gateway = new Intraface_modules_currency_Currency_Gateway($this->registry->get('doctrine'));
            $currencies = $gateway->findAllWithExchangeRate();
        }
        else {
            $currencies = false;
        }

        $webshop_module = $this->registry->get('kernel')->module('shop');
        $settings = $webshop_module->getSetting('show_online');
        $languages = new Intraface_modules_language_Languages;
        $langs = $languages->getChosenAsArray();

        $data = array(
            'data' => $data,
            'settings' => $settings,
            'currencies' => $currencies,
            'languages' => $langs);
        return $this->getError()->view() . $this->render(dirname(__FILE__) . '/tpl/edit.tpl.php', $data);
    }

    function POST()
    {
        if (!$this->isValid()) {
            return $this->GET();
        }

        $doctrine = $this->registry->get('doctrine');

        try {
            if (is_numeric($this->context->name)) {
                $shop = $this->getModel();
            } else {
                $shop = new Intraface_modules_shop_Shop;
                $shop->intranet_id = $this->registry->get('kernel')->intranet->getId();
            }
            $shop->fromArray($this->POST->getArrayCopy());
            if (isset($this->POST['confirmation_add_contact_url']) AND $this->POST['confirmation_add_contact_url'] == 1) {
                $shop->confirmation_add_contact_url = 1;
            } else {
                $shop->confirmation_add_contact_url = 0;
            }
            if (isset($this->POST['payment_link_add']) AND $this->POST['payment_link_add'] == 1) {
                $shop->payment_link_add = 1;
            } else {
                $shop->payment_link_add = 0;
            }
            if (isset($this->POST['send_confirmation']) AND $this->POST['send_confirmation'] == 1) {
                $shop->send_confirmation = 1;
            } else {
                $shop->send_confirmation = 0;
            }
            $shop->save();
        } catch (Exception $e) {
            throw $e;
        }

        throw new k_http_Redirect($this->url('../'));
    }
}