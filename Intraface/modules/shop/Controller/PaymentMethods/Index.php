<?php
class Intraface_modules_shop_Controller_PaymentMethods_Index extends k_Controller
{
    function getShopId()
    {
        return $this->context->name;
    }

    function GET()
    {
        $doctrine = $this->registry->get('doctrine');
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->getShopId());

        $this->document->title = $this->__('Payment methods for') . ' ' . $shop->name;

        $this->document->options = array($this->url('../') => 'Close');

        $method = new Intraface_modules_debtor_PaymentMethod;
        $methods = $method->getAll();

        $chosen = $this->getPaymentMethodsForShop();

        $data = array('shop' => $shop, 'methods' => $methods, 'chosen' => $chosen);

        return $this->render(dirname(__FILE__) . '/../tpl/paymentmethods-index.tpl.php', $data);
    }

    function getPaymentMethodsForShop()
    {
        $doctrine = $this->registry->get('doctrine');
        $methods = Doctrine::getTable('Intraface_modules_shop_PaymentMethods')->findByShopId($this->getShopId());
        $m = array();
        foreach ($methods as $method) {
            $m[$method->getPaymentMethodKey()]['id'] = $method->getId();
            $m[$method->getPaymentMethodKey()]['text'] = $method->getText();
        }
        return $m;
    }

    function flushPaymentMethods()
    {
        $doctrine = $this->registry->get('doctrine');
        $methods = Doctrine::getTable('Intraface_modules_shop_PaymentMethods')->findByShopId($this->getShopId());
        foreach ($methods as $method) {
            $method->delete();
        }
    }

    function POST()
    {
        $doctrine = $this->registry->get('doctrine');
        $paymentmethods = $this->getPaymentMethodsForShop();
        $this->flushPaymentMethods();

        foreach ($this->POST['method'] as $key => $value) {
            if (!empty($paymentmethods[$this->POST['method'][$key]])) {
                $method = Doctrine::getTable('Intraface_modules_shop_PaymentMethods')->findOneById($paymentmethods[$this->POST['method'][$key]]['id']);
                if (!$method) {
                    $method = new Intraface_modules_shop_PaymentMethods();
                    $method->paymentmethod_key = $this->POST['method'][$key];
                    $method->text = $this->POST['text'][$key];
                    $method->shop_id = $this->getShopId();
                    $method->save();
                } else {
                    $method->text = $this->POST['text'][$key];
                    $method->save();
                }
            } else {
                $method = new Intraface_modules_shop_PaymentMethods();
                $method->paymentmethod_key = $this->POST['method'][$key];
                $method->text = $this->POST['text'][$key];
                $method->shop_id = $this->getShopId();
                $method->save();
            }
        }

        throw new k_http_Redirect($this->url());
    }
}