<?php
class Intraface_modules_debtor_PaymentMethod
{
    /*
    private $kernel;

    function __construct($kernel)
    {
    	$this->kernel = $kernel;
    }
    */

    /**
     * Do not change the key for the payment methods
     *
     * @return array The possible payment types
     */
    public function getTypes()
    {
        $types[0] = 'None';
        $types[1] = 'BankTransfer';
        $types[2] = 'GiroPayment01';
        $types[3] = 'GiroPayment71';
        $types[4] = 'CashOnDelivery';
        $types[5] = 'OnlinePayment';
        $types[6] = 'EAN';

        return $types;
    }

    /**
     * Returns specific payment method
     */
    public function getByName($method)
    {
        if (!ereg("^[a-zA-Z0-9]+$", $method)) {
            throw new Exception('Invalid method name "'.$method.'"');
        }

        $name = 'Intraface_modules_debtor_PaymentMethod_'.$method;

        return new $name;
    }

    /**
     * Returns a payment method by id
     *
     * @return object
     */
    public function getById($key, $id = 0)
    {
        $types = $this->getTypes();
        if (!isset($types[$key])) {
            throw new Exception('Invalid payment method id');
        }

        return $this->getByName($types[$key]);
    }

    /**
     * Returns a payment method by key
     *
     * @return object
     */
    public function getByKey($key)
    {
        return $this->getById($key);
    }

    /**
     * Returns possible payment methods
     *
     * @return array
     */
    public function getAll()
    {
        $types = $this->getTypes();
        $t = array();
        foreach ($types as $key => $type) {
            $t[$key] = $this->getByName($type);
        }
        return $t;
    }

    function getChosenAsArray($shop_id)
    {
        $payment_methods = array();

        $methods = Doctrine::getTable('Intraface_modules_shop_PaymentMethods')->findByShopId($shop_id);
        foreach ($methods as $method) {
            $gateway = new Intraface_modules_debtor_PaymentMethod();
            $m = $gateway->getByKey($method->getPaymentMethodKey());

            $payment_methods[] = array(
                'key' => $method->getPaymentMethodKey(),
                'identifier' => $m->getIdentifier(),
                'description' => $m->getDescription(),
                //'text' => $method->getText()
            );
        }

        return $payment_methods;
    }
}
