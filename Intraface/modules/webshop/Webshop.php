<?php
/**
 * Webshop sørger for at holde styr webshoppen.
 *
 * @todo Indstilling af porto - skal det være et standardprodukt på alle ordrer?
 *
 * @todo Standardprodukter på ordrer.
 *
 * @todo Opførsel ift. lager i onlineshoppen.
 *
 * @todo Bør kunne tage højde for en tidsangivelse på produkterne
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 * @see Basket
 * @see WebshopServer.php / XML-RPC-serveren i xmlrpc biblioteket
 * @see Order
 * @see Contact
 */

class Webshop
{
    var $kernel;
    var $basket;
    var $order;
    var $contact;
    var $error;
    private $session_id;

    /**
     * Construktor
     *
     * @param object $kernel     Kernel object
     * @param string $session_id Unikt session id
     *
     * @return void
     */
    public function __construct($kernel, $session_id)
    {
        if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
            trigger_error('Webshopmodulet har brug for Kernel', E_USER_ERROR);
        }

        $this->kernel = $kernel;
        $this->kernel->useModule('debtor');
        $this->kernel->useModule('order');
        $this->kernel->useModule('product');
        $this->kernel->useModule('contact');

        $this->session_id = $session_id;
        $this->basket = $this->createBasket();

        $this->error = new Error;
    }

    /**
     * Convenience method to create the basket
     *
     * @return object
     */
    private function createBasket()
    {
        return new Basket($this, $this->session_id);
    }

    /**
     * Gets the basket
     *
     * @return object
     */
    public function getBasket()
    {
        return $this->basket;
    }

    private function createOrder($input)
    {
        // kunne være vakst om denne tjekkede om kontaktpersonen allerede findes
        if (!empty($input['contact_id'])) {
            $this->contact = new Contact($this->kernel, $input['contact_id']);
            $value['contact_id'] = $this->contact->get('id');
            $value['contact_address_id'] = $this->contact->address->get('id');
            $value['contact_person_id'] = 0;
        } else {
            $this->contact = new Contact($this->kernel);

            // sørger for at tjekke om det er et firma
            if (!empty($input['contactperson'])) {
                $input['type'] = 1; // firma
            }

            // sets preffered invoice to email. Should be a setting in webshop.
            $input['preferred_invoice'] = 2;

            // opdaterer kontakten
            if (!$contact_id = $this->contact->save($input)) {
                $this->error->message = array_merge($this->error->message, $this->contact->error->message);
                return false;
            }
            $contact_person_id = 0;

            if ($input['type'] == 1) { // firma
                $contactperson = new ContactPerson($this->contact);
                if (!$contact_person_id = $contactperson->save(array('name'=>$input['contactperson']))) {
                    $this->error->message = array_merge($this->error->message, $contactperson->error->message);
                    return false;
                }
            }
            $value['contact_id'] = $contact_id;
            $value['contact_address_id'] = $this->contact->address->get('id');
            $value['contact_person_id'] = $contact_person_id;
        }

        $value['this_date'] = date('d-m-Y');
        $value['due_date'] = date('d-m-Y');
        $value['description'] = $input['description'];
        $value['internal_note'] = $input['internal_note'];
        $value['message'] = $input['message'];

        if(isset($input['customer_coupon']) && $input['customer_coupon'] != '') {
            if($value['message'] != '') $value['message'] .= "\n\n";
            $value['message'] .= "Kundekupon:". $input['customer_coupon'];
        }

        if(isset($input['customer_comment']) && $input['customer_comment'] != '') {
            if($value['message'] != '') $value['message'] .= "\n\n";
            $value['message'] .= "Kommentar:\n". $input['customer_comment'];
        }

        $this->order = new Debtor($this->kernel, 'order');
        if (!$order_id = $this->order->update($value, 'webshop')) {
            $this->error->message = array_merge($this->error->message, $this->order->error->message);
            return false;
        }

        return $order_id;

    }

    /**
     * Places the order and utilizes basket
     *
     * @param array $input    Array with customer data
     * @param array $products Array with products
     *
     * @return integer Order id
     */
    public function placeManualOrder($input = array(), $products = array())
    {
        $order_id = $this->createOrder($input);

        $this->addOrderLines($products);

        $this->sendEmail($order_id);

        return $order_id;
    }

    /**
     * Places the order and utilizes basket
     *
     * @param array $input Array with customer data
     *
     * @return integer Order id
     */
    public function placeOrder($input)
    {
        /*
        // kunne være vakst om denne tjekkede om kontaktpersonen allerede findes
        if (!empty($input['contact_id'])) {
            $this->contact = new Contact($this->kernel, $input['contact_id']);
            $value['contact_id'] = $this->contact->get('id');
            $value['contact_address_id'] = $this->contact->address->get('id');
            $value['contact_person_id'] = 0;
        } else {
            $this->contact = new Contact($this->kernel);

            // sørger for at tjekke om det er et firma
            if (!empty($input['contactperson'])) {
                $input['type'] = 1; // firma
            }

            // sets preffered invoice to email. Should be a setting in webshop.
            $input['preferred_invoice'] = 2;

            // opdaterer kontakten
            if (!$contact_id = $this->contact->save($input)) {
                $this->error->message = array_merge($this->error->message, $this->contact->error->message);
                return false;
            }
            $contact_person_id = 0;

            if ($input['type'] == 1) { // firma
                $contactperson = new ContactPerson($this->contact);
                if (!$contact_person_id = $contactperson->save(array('name'=>$input['contactperson']))) {
                    $this->error->message = array_merge($this->error->message, $contactperson->error->message);
                    return false;
                }
            }
            $value['contact_id'] = $contact_id;
            $value['contact_address_id'] = $this->contact->address->get('id');
            $value['contact_person_id'] = $contact_person_id;
        }

        $value['this_date'] = date('d-m-Y');
        $value['due_date'] = date('d-m-Y');
        $value['description'] = $input['description'];
        $value['internal_note'] = $input['internal_note'];
        $value['message'] = $input['message'];

        if(isset($input['customer_coupon']) && $input['customer_coupon'] != '') {
            if($value['message'] != '') $value['message'] .= "\n\n";
            $value['message'] .= "Kundekupon:". $input['customer_coupon'];
        }

        if(isset($input['customer_comment']) && $input['customer_comment'] != '') {
            if($value['message'] != '') $value['message'] .= "\n\n";
            $value['message'] .= "Kommentar:\n". $input['customer_comment'];
        }

        $this->order = new Debtor($this->kernel, 'order');
        if (!$order_id = $this->order->update($value, 'webshop')) {
            $this->error->message = array_merge($this->error->message, $this->order->error->message);
            return false;
        }
        */

        $order_id = $this->createOrder($input);

        $products = $this->basket->getItems();

        $this->addOrderLines($products);

        $this->basket->reset();

        $this->sendEmail($order_id);

        return $order_id;
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
        foreach ($products AS $product) {
            $this->order->loadItem();
            $value['product_id'] = $product['id'];
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
    private function sendEmail($order_id)
    {
        $this->kernel->useShared('email');
        $email = new Email($this->kernel);

        if (!$email->save(array('contact_id' => $this->contact->get('id'),
                                'subject' => 'Bekræftelse på bestilling #' . $order_id,
                                'body' => $this->kernel->setting->get('intranet', 'webshop.confirmation_text') . "\n" . $this->contact->getLoginUrl() . "\n\nVenlig hilsen\n" . $this->kernel->intranet->address->get('name'),
                                'from_email' => $this->kernel->intranet->address->get('email'),
                                'from_name' => $this->kernel->intranet->address->get('name'),
                                'type_id' => 12, // webshop
                                'belong_to' => $order_id))) {
            echo $email->error->view();
        }

        if(!$email->send()) {
            echo $email->error->view();
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
            return $payment_id;
        } else {
            $onlinepayment->error->view();
            return 0;
        }
    }

    /**
     * Returns receipt text
     *
     * @return array with receipt
     */
    public function getReceiptText()
    {
        return array('receipt_text' => $this->kernel->setting->get('intranet','webshop.webshop_receipt'));
    }

    /**
     * Sets the order as sent
     *
     * @param integer $order_id
     *
     * @return boolean
     */
    public function setSent($order_id)
    {
        $order_id = (int)$order_id;
        if ($order_id == 0) {
            return 0;
        }
        $debtor = Debtor::factory($order_id);
        return $debtor->setSent();
    }
}
?>