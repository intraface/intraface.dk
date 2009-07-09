<?php
class Intraface_XMLRPC_Controller extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'adminx') {
            return 'Intraface_XMLRPC_Admin_Controller';
        } elseif ($name == 'cmsx') {
            return 'Intraface_XMLRPC_CMS_Controller';
        } elseif ($name == 'contactx') {
            return 'Intraface_XMLRPC_Contact_Controller';
        } elseif ($name == 'debtorx') {
            return 'Intraface_XMLRPC_Debtor_Controller';
        } elseif ($name == 'newsletterx') {
            return 'Intraface_XMLRPC_Newsletter_Controller';
        } elseif ($name == 'onlinepaymentx') {
            return 'Intraface_XMLRPC_OnlinePayment_Controller';
        } elseif ($name == 'shopx') {
            return 'Intraface_XMLRPC_Shop_Controller';
        }
    }

    function renderHtml()
    {
        return '
        <h2>Old servers</h2>
        <ul>
            <li><a href="cms/server2.php">CMS</a></li>
            <li><a href="contact/server.php">Contact</a></li>
            <li><a href="debtor/server.php">Debtor</a></li>
            <li><a href="newsletter/server.php">Newsletter</a></li>
            <li><a href="shop/server3.php">Shop</a></li>
        </ul>
        <h2>New servers</h2>
        <ul>
            <li><a href="cmsx">CMS</a></li>
            <li><a href="contactx">Contact</a></li>
            <li><a href="debtorx">Debtor</a></li>
            <li><a href="newsletterx">Newsletter</a></li>
            <li><a href="shopx">Shop</a></li>
        </ul>

        ';
    }
}