<?php
class Install_Helper_Debtor
{
    private $kernel;
    private $db;

    public function __construct($kernel, $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    public function createInvoice()
    {
        require_once dirname (__FILE__) . '/Contact.php';
        $contact = new Install_Helper_Contact($this->kernel, $this->db);
        $contact_id = $contact->create();

        require_once dirname(__FILE__) . '/Product.php';
        $product = new Install_Helper_Product($this->kernel, $this->db);
        $product_id = $product->create();

        require_once 'Intraface/modules/invoice/Invoice.php';
        $debtor = new Invoice($this->kernel);
        $id = $debtor->update(array(
            'contact_id' => $contact_id,
            'description' => 'Test invoice',
            'this_date' => date('d-m-Y'),
            'due_date' => date('d-m-Y', time()+14*60*60*24)));

        $debtor->loadItem();
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3, 'description' => 'Test description on product'));
    }

    public function createOrder()
    {
        require_once dirname(__FILE__) . '/Contact.php';
        $contact = new Install_Helper_Contact($this->kernel, $this->db);
        $contact_id = $contact->create();

        require_once dirname(__FILE__) . '/Product.php';
        $product = new Install_Helper_Product($this->kernel, $this->db);
        $product_id = $product->create();

        require_once 'Intraface/modules/order/Order.php';
        $debtor = new Order($this->kernel);
        $id = $debtor->update(array(
            'contact_id' => $contact_id,
            'description' => 'Test invoice',
            'this_date' => date('d-m-Y'),
            'due_date' => date('d-m-Y', time()+14*60*60*24)));

        $debtor->loadItem();
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3, 'description' => 'Test description on product'));
    }

    public function createOrderFromShop()
    {
        require_once dirname(__FILE__) . '/Contact.php';
        $contact = new Install_Helper_Contact($this->kernel, $this->db);
        $contact_id = $contact->create();

        require_once dirname(__FILE__) . '/Product.php';
        $product = new Install_Helper_Product($this->kernel, $this->db);
        $product_id = $product->create();

        require_once 'Intraface/modules/order/Order.php';
        $debtor = new Order($this->kernel);
        $id = $debtor->update(array(
            'contact_id' => $contact_id,
            'description' => 'From shop',
            'this_date' => date('d-m-Y'),
            'due_date' => date('d-m-Y', time()+14*60*60*24)), 'webshop', 1);

        $debtor->loadItem();
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3, 'description' => 'Test description on product'));

    }
}
