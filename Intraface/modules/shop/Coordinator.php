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
        if ($this->basket) return $this->basket;
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

            // sørger for at tjekke om det er et firma
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

        if (isset($input['customer_ean'])) {
            $input['ean'] = $input['customer_ean'];
        }

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


        $this->order = new Debtor($this->kernel, 'order');
        $order_id = $this->order->update($value, 'webshop');

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
     *
     * @return integer Order id
     */
    public function placeManualOrder($input = array(), $products = array())
    {
        $order_id = $this->createOrder($input);
        if ($order_id == 0) {
            $this->error->set('unable to create the order');
            return false;
        }

        if (!$this->addOrderLines($products)) {
            $this->error->set('unable add products to the order');
            return false;
        }

        if (!$this->sendEmail($order_id)) {
            $this->error->set('unable to send email to the customer');
            return false;
        }

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

        if (!$this->sendEmail($order_id)) {
            $this->error->set('unable to send email to the customer');
            return false;
        }

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
            $this->error->merge($email->error->getMessage());
            return false;
        }

        if (!$email->send()) {
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
     * @todo why return an array
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
    private function setSent($order_id)
    {
        $order_id = (int)$order_id;
        if ($order_id == 0) {
            return 0;
        }
        $debtor = Debtor::factory($this->kernel, (int)$order_id);
        return $debtor->setSent();
    }
}