<?php
/**
 * Webshop s�rger for at holde styr webshoppen.
 *
 * @todo Indstilling af porto - skal det v�re et standardprodukt p� alle ordrer?
 *
 * @todo Standardprodukter p� ordrer.
 *
 * @todo Opf�rsel ift. lager i onlineshoppen.
 *
 * @todo B�r kunne tage h�jde for en tidsangivelse p� produkterne
 *
 * @todo traekke send email ud.
 *
 * @package Intraface_Shop
 * @author Lars Olesen <lars@legestue.net>
 *
 * @see Basket
 * @see WebshopServer.php / XML-RPC-serveren i xmlrpc biblioteket
 * @see Order
 * @see Contact
 */
class Intraface_modules_shop_Coordinator
{
    /**
     * @var object
     */
    public $kernel;

    /**
     * @var object
     */
    public $basket;

    /**
     * @var object
     */
    public $shop;

    /**
     * @var object
     */
    private $order;
    private $onlinepayment;

    /**
     * @var object
     */
    private $contact;

    /**
     * @var object
     */
    public $error;

    /**
     * @var private
     */
    public $session_id;

    /**
     * Construktor
     *
     * @param object $kernel     Kernel object
     * @param string $session_id Unikt session id
     *
     * @return void
     */
    public function __construct($kernel, $shop, $session_id)
    {
        $this->kernel = $kernel;
        $this->kernel->useModule('debtor');
        $this->kernel->useModule('order');
        $this->kernel->useModule('product');
        $this->kernel->useModule('contact');

        $this->session_id = $session_id;
        $this->intranet = $kernel->intranet;
        $this->shop = $shop;

        $this->error = new Intraface_Error;
    }

    function getShop()
    {
        return $this->shop;
    }

    /**
     * Get basket
     *
     * @return object
     */
    public function getBasket()
    {
        if ($this->basket) {
            return $this->basket;
        }
        return ($this->basket = new Intraface_modules_shop_Basket(MDB2::singleton(DB_DSN), $this->intranet, $this, $this->shop, $this->session_id));
    }

    /**
     * Places the order and utilizes basket
     *
     * @param array $input Array with customer data
     *
     * @return integer Order id
     */
    private function createOrder($input)
    {
        if (isset($input['contact_id']) && (int)$input['contact_id'] > 0) {
            $this->contact = new Contact($this->kernel, (int)$input['contact_id']);

            $contact_person_id = 0;
            // It is a company and contactperson is given. We try to see if we can find the contact person.
            if (isset($input['contactperson']) && $input['contactperson'] != '') {
                $input['type'] = 'corporation';

                // If the contact is a company their might already be a contact person.
                if ($this->contact->get('type') == 'company' && isset($this->contact->contactperson)) {
                    $contact_persons = $this->contact->contactperson->getList();
                    foreach ($contact_persons AS $contact_person) {
                        // This is only a comparing on name, this might not be enough.
                        if ($contact_person['name'] == $input['contactperson']) {
                            $contact_person_id = $contact_person['id'];
                            break;
                        }
                    }
                }
            }
        } else {
            $this->contact = new Contact($this->kernel);
            $contact_person_id = 0;

            // s�rger for at tjekke om det er et firma
            if (isset($input['contactperson']) && $input['contactperson'] != '') {
                $input['type'] = 'corporation'; // firma
            }

            if (isset($input['customer_ean']) && $input['customer_ean'] != '') {
                // sets preffered invoice to electronic.
                $input['type'] = 'corporation'; // firma
                $input['preferred_invoice'] = 3;
            } else {
                // sets preffered invoice to email. Should be a setting in webshop.
                $input['preferred_invoice'] = 2;
            }
        }
        /*
        if (isset($input['customer_ean'])) {
            $input['ean'] = $input['customer_ean'];
        }
        */

        // opdaterer kontakten
        if (!$contact_id = $this->contact->save($input)) {
            $this->error->merge($this->contact->getError()->getMessage());
            return false;
        }

        // we update/add the contactperson.
        if (isset($input['type']) && $input['type'] == 'corporation') { // firma
            $this->contact->loadContactPerson($contact_person_id);
            settype($input['contactperson'], 'string');
            settype($input['contactemail'], 'string');
            settype($input['contactphone'], 'string');
            if (!$contact_person_id = $this->contact->contactperson->save(array('name'=>$input['contactperson'], 'email'=>$input['contactemail'], 'phone'=>$input['contactphone']))) {
                $this->error->merge($this->contact->getError()->getMessage());
                return false;
            }
        }

        // currency
        if (!empty($input['currency']) && strtoupper($input['currency']) != 'DKK' && $this->kernel->intranet->hasModuleAccess('currency')) {
            $this->kernel->useModule('currency', true); /* true: ignore user access */

            $currency_gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection(DB_DSN));
            if (false !== ($currency = $currency_gateway->findByIsoCode($input['currency']))) {
                $value['currency'] = $currency;
            }
        }

        $value['contact_id'] = $this->contact->get('id');
        $value['contact_address_id'] = $this->contact->address->get('id');
        $value['contact_person_id'] = $contact_person_id;

        $value['this_date'] = date('d-m-Y');
        $value['due_date'] = date('d-m-Y');
        settype($input['description'], 'string');
        $value['description'] = $input['description'];
        settype($input['internal_note'], 'string');
        $value['internal_note'] = $input['internal_note'];
        settype($input['message'], 'string');
        $value['message'] = $input['message'];

        if (isset($input['customer_coupon']) && $input['customer_coupon'] != '') {
            if ($value['message'] != '') $value['message'] .= "\n\n";
            $value['message'] .= "Kundekupon:". $input['customer_coupon'];
        }

        if (isset($input['customer_comment']) && $input['customer_comment'] != '') {
            if ($value['message'] != '') $value['message'] .= "\n\n";
            $value['message'] .= "Kommentar:\n". $input['customer_comment'];
        }

        if (isset($input['payment_method']) && is_array($input['payment_method']) && !empty($input['payment_method'])) {
            $value['payment_method'] = $input['payment_method']['key'];
        }

        $this->order = new Debtor($this->kernel, 'order');
        $order_id = $this->order->update($value, 'webshop', $this->shop->getId());

        if ($order_id == 0) {
            $this->error->merge($this->order->error->message);
            return false;
        }

        return $order_id;
    }

    /**
     * Places the order and utilizes basket
     *
     * @param array $input    Array with customer data
     * @param array $products Array with products
     * @param object $mailer mailer to send e-mail
     *
     * @return integer Order id
     */
    public function placeManualOrder($input = array(), $products = array(), $mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }

        $order_id = $this->createOrder($input);

        if ($order_id == 0) {
            $this->error->set('unable to create the order');
            return false;
        }

        if (!$this->addOrderLines($products)) {
            $this->error->set('unable add products to the order');
            return false;
        }

        if (!$this->sendEmail($order_id, $mailer)) {
            $this->error->set('unable to send email to the customer');
            return false;
        }

        return $order_id;
    }

    /**
     * Places the order and utilizes basket
     *
     * @param array $input Array with customer data
     * @param object $mailer Mailer object to send e-mail
     *
     * @return integer Order id
     */
    public function placeOrder($input, $mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }

        if (!$order_id = $this->createOrder($input)) {
            $this->error->set('unable to create the order');
            return false;
        }

        $products = $this->getBasket()->getItems();

        if (!$this->addOrderLines($products)) {
            $this->error->set('unable add products to the order');
            return false;
        }

        $this->getBasket()->reset();

        if ($this->getShop()->sendConfirmation()) {
            if (!$this->sendEmail($order_id, $mailer)) {
                $this->error->set('unable to send email to the customer');
                return false;
            }
        }

        return $order_id;
    }

    public function getOrderIdentifierKey()
    {
        if (!empty($this->order) && is_object($this->order) && $this->order->get('id') != 0) {
            return $this->order->get('identifier_key');
        }
        throw new Exception('No valid order was present to return identifier from IN Coordinate->getOrderIdentifierKey()');
    }

    /**
     * Adds the order lines
     *
     * @param array $products
     *
     * @return boolean
     */
    private function addOrderLines($products = array())
    {
        foreach ($products as $product) {
            $this->order->loadItem();
            $value['product_id'] = $product['product_id'];
            $value['product_variation_id'] = $product['product_variation_id'];
            $value['quantity'] = $product['quantity'];
            $value['description'] = $product['text'];
            $this->order->item->save($value);
        }

        return true;
    }

    /**
     * Sends the email
     *
     * @param integer $order_id
     *
     * @return boolean
     */
    private function sendEmail($order_id, $mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object is needed');
        }

        $this->kernel->useShared('email');
        $email = new Email($this->kernel);

        if ($this->shop->getConfirmationSubject()) {
            $subject = $this->shop->getConfirmationSubject() . ' (#' . $this->order->get('number') . ')';
        } else {
            $subject = 'Bekræftelse på bestilling (#' . $this->order->get('number') . ')';
        }

        $body = $this->shop->getConfirmationText();

        if ($this->shop->showPaymentUrl()) {
            $body .=  "\n\n" . $this->shop->getPaymentUrl() . $this->order->getIdentifier();
        }

        // @todo improve this table
        //       mabye we should write a couple of outputters of an invoice
        //       we should use a calculator so we can get vat and total easily on
        $table = new Console_Table;
        foreach ($this->order->getItems() as $item) {
            if ($this->order->getCurrency()) {
                $amount = $item["amount_currency"]->getAsLocal('da_dk', 2);
                $currency_iso_code = $this->order->getCurrency()->getType()->getIsoCode();
            } else {
                $amount = $item["amount"]->getAsLocal('da_dk', 2);
                $currency_iso_code = 'DKK';
            }

            $table->addRow(array(round($item["quantity"]), substr($item["name"], 0, 40), $currency_iso_code.' ' . $amount));
        }

        $body .= "\n\n" . $table->getTable();

        if ($this->shop->getConfirmationGreeting()) {
            $body .=  "\n\n" . $this->shop->getConfirmationGreeting();
        } else {
            $body .= "Venlig hilsen\n".  $this->kernel->intranet->address->get('name');
        }

        if ($this->shop->showLoginUrl()) {
            $body .=  "\n\n" . $this->contact->getLoginUrl();
        }

        if (!$email->save(array('contact_id' => $this->contact->get('id'),
                                'subject' => $subject,
                                'body' => $body,
                                'from_email' => $this->kernel->intranet->address->get('email'),
                                'from_name' => $this->kernel->intranet->address->get('name'),
                                'type_id' => 12, // webshop
                                'belong_to' => $order_id))) {
            $this->error->merge($email->error->getMessage());
            return false;
        }

        if (!$email->send($mailer)) {
            $this->error->merge($email->error->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Places the order and utilizes basket
     *
     * @param integer $order_id
     * @param integer $transaction_number
     * @param integer $transaction_status
     * @param float   $transaction_amount
     *
     * @return boolean
     */
    public function addOnlinePayment($order_id, $transaction_number, $transaction_status, $amount)
    {
        if ($order_id == 0) {
            return 0;
        }

        $this->order = new Debtor($this->kernel, $order_id);
        $this->contact = $this->order->getContact();

        if (!$this->kernel->intranet->hasModuleAccess('onlinepayment')) {
            return 0;
        }

        // hvad skal den her sikre?
        if (is_object($this->kernel->user) AND $this->kernel->user->hasModuleAccess('onlinepayment')) {
            return 0;
        }

        $this->kernel->useModule('onlinepayment');
        $onlinepayment = new OnlinePayment($this->kernel);

        $values = array('belong_to'          => 'order',
                        'belong_to_id'       => (int)$order_id,
                        'transaction_number' => $transaction_number,
                        'transaction_status' => $transaction_status,
                        'amount'             => $amount);

        if ($payment_id = $onlinepayment->save($values)) {
            $this->onlinepayment = $onlinepayment;
            if ($this->sendEmailOnOnlinePayment($payment_id)) {
                throw new Exception('Could not send email as receipt for onlinepayment');
            }
            return $payment_id;
        } else {
            return 0;
        }
    }

    private function sendEmailOnOnlinePayment($payment_id, $mailer = null)
    {
        if ($mailer === null) {
            $mailer = Intraface_Mail::factory();
        }

        $this->kernel->useShared('email');
        $email = new Email($this->kernel);

        $subject = 'Bekræftelse på betaling (#' . $payment_id . ')';
        $body = 'Vi har modtaget din betaling. Hvis din ordre var afsendt inden kl. 12.00, sender vi den allerede i dag.';
        $body .= "\n\nVenlig hilsen\n".  $this->kernel->intranet->address->get('name');

        if (!$email->save(array('contact_id' => $this->contact->get('id'),
                                'subject' => $subject,
                                'body' => $body,
                                'from_email' => $this->kernel->intranet->address->get('email'),
                                'from_name' => $this->kernel->intranet->address->get('name'),
                                'type_id' => 13, // onlinepayment
                                'belong_to' => $payment_id))) {
            $this->error->merge($email->error->getMessage());
            return false;
        }

        if (!$email->send($mailer)) {
            $this->error->merge($email->error->getMessage());
            return false;
        }

        return true;
    }


    /**
     * Returns receipt text
     *
     * @todo why return an array
     *
     * @return array with receipt
     */
    public function getReceiptText()
    {
        return array('receipt_text' => $this->getShop()->receipt);
    }

    /**
     * Sets the order as sent
     *
     * @param integer $order_id
     *
     * @return boolean
     */
    private function setSent($order_id)
    {
        $order_id = (int)$order_id;
        if ($order_id == 0) {
            return 0;
        }
        $debtor = Debtor::factory($this->kernel, (int)$order_id);
        return $debtor->setSent();
    }

    /**
     * Returns payment methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $payment_methods = array();

        $method = new Intraface_modules_debtor_PaymentMethod();
        $shop_id = $this->shop->getId();
        $payment_methods = $method->getChosenAsArray($shop_id);

        if (empty($payment_methods)) {
            if ($this->kernel->intranet->hasModuleAccess('onlinepayment')) {
                $payment_methods[] = array(
                    'key' => 5,
                    'identifier' => 'OnlinePayment',
                    'description' => 'Online payment',
                    'text' => '');
            } else {
                $payment_methods[] = array(
                    'key' => 4,
                    'identifier' => 'CashOnDelivery',
                    'description' => 'Cash on delivery',
                    'text' => '');
            }
        }

        return $payment_methods;
    }

    /**
     * Returns payment method key from identifier
     *
     * @param string $payment_method
     *
     * @return integer payment method key
     */
    public function getPaymentMethodKeyFromIdenfifier($payment_method)
    {
        $methods = $this->getPaymentMethods();
        foreach ($methods as $method) {
            if ($method['identifier'] == $payment_method) {
                return $method['key'];
            }
        }

        throw new Exception('Invalid payment method "'.$payment_method.'"');
    }

    /**
     * Returns payment method from key
     *
     * @param string $payment_method_key
     *
     * @return array payment method
     */
    public function getPaymentMethodFromKey($payment_method_key)
    {
        $methods = $this->getPaymentMethods();
        foreach ($methods as $method) {
            if ($method['key'] == $payment_method_key) {
                return $method;
            }
        }

        throw new Exception('Invalid payment method key "'.$payment_method_key.'"');
    }
}