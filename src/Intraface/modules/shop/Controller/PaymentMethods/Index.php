<?php
class Intraface_modules_shop_Controller_PaymentMethods_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->getShopId());

        $this->document->setTitle('Payment methods for') . ' ' . $shop->name;

        $this->document->options = array($this->url('../') => 'Close');

        $method = new Intraface_modules_debtor_PaymentMethod;
        $methods = $method->getAll();

        $chosen = $this->getPaymentMethodsForShop();

        $data = array('shop' => $shop, 'methods' => $methods, 'chosen' => $chosen);
        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/paymentmethods-index');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $paymentmethods = $this->getPaymentMethodsForShop();
        $this->flushPaymentMethods();

        $post = $this->body();
        foreach ($post['method'] as $key => $value) {
            if (!empty($paymentmethods[$post['method'][$key]])) {
                $method = Doctrine::getTable('Intraface_modules_shop_PaymentMethods')->findOneById($paymentmethods[$post['method'][$key]]['id']);
                if (!$method) {
                    $method = new Intraface_modules_shop_PaymentMethods();
                    $method->paymentmethod_key = $post['method'][$key];
                    $method->text = $post['text'][$key];
                    $method->shop_id = $this->getShopId();
                    $method->save();
                } else {
                    $method->text = $post['text'][$key];
                    $method->save();
                }
            } else {
                $method = new Intraface_modules_shop_PaymentMethods();
                $method->paymentmethod_key = $post['method'][$key];
                $method->text = $post['text'][$key];
                $method->shop_id = $this->getShopId();
                $method->save();
            }
        }

        return new k_SeeOther($this->url());
    }

    function getPaymentMethodsForShop()
    {
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
        $methods = Doctrine::getTable('Intraface_modules_shop_PaymentMethods')->findByShopId($this->getShopId());
        foreach ($methods as $method) {
            $method->delete();
        }
    }

    function getShopId()
    {
        return $this->context->name();
    }
}