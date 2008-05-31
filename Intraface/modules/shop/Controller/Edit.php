<?php
require_once 'Intraface/Error.php';

class Intraface_modules_shop_Controller_Edit extends k_Controller
{
    function isValid()
    {
        $error = new Intraface_Error();
        $validator = new Validator($error);
        $validator->isNumeric($this->POST['show_online'], 'show_online skal være et tal');
        $validator->isString($this->POST['description'], 'confirmation text is not valid');
        $validator->isString($this->POST['confirmation'], 'confirmation text is not valid');
        $validator->isString($this->POST['receipt'], 'webshop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');

        return !$error->isError();
    }

    function getModel()
    {
        $doctrine = $this->registry->get('doctrine');
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name);
        return $shop;
    }

    function GET()
    {
        $data = array();

        if (is_numeric($this->context->name)) {
            $shop = $this->getModel();
            $data = $shop->toArray();
        }

        $webshop_module = $this->registry->get('kernel')->module('shop');
        $settings = $webshop_module->getSetting('show_online');

        $data = array('data' => $data, 'settings' => $settings);
        return $this->render(dirname(__FILE__) . '/tpl/edit.tpl.php', $data);
    }

    function POST()
    {
        if (!$this->isValid()) {
            throw new Exception('Values not valid');
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
            $shop->save();
        } catch (Exception $e) {
            throw $e;
        }

        throw new k_http_Redirect($this->url('../'));
    }
}