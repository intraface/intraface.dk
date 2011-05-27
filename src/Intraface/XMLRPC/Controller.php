<?php
class Intraface_XMLRPC_Controller extends k_Component
{
    function dispatch()
    {
        if ($this->identity()->anonymous()) {
            throw new k_NotAuthorized();
        }
        return parent::dispatch();
    }

    function map($name)
    {
        if ($name == 'admin') {
            return 'Intraface_XMLRPC_Admin_Controller';
        } elseif ($name == 'cms') {
            return 'Intraface_XMLRPC_CMS_Controller';
        } elseif ($name == 'contact') {
            return 'Intraface_XMLRPC_Contact_Controller';
        } elseif ($name == 'debtor') {
            return 'Intraface_XMLRPC_Debtor_Controller';
        } elseif ($name == 'newsletter') {
            return 'Intraface_XMLRPC_Newsletter_Controller';
        } elseif ($name == 'onlinepayment') {
            return 'Intraface_XMLRPC_OnlinePayment_Controller';
        } elseif ($name == 'shop') {
            return 'Intraface_XMLRPC_Shop_Controller';
        }
    }

    function renderHtml()
    {
        $servers = array(
            'admin' => 'Admin',
            'cms' => 'CMS',
            'contact' => 'Contact',
            'debtor' => 'Debtor',
            'newsletter' => 'Newsletter',
            'onlinepayment' => 'Onlinepayment',
            'shop' => 'Shop'
        );

        return '
        <h2>Intraface xmlrpc servers</h2>
        <ul>
            <li><a href="'.$this->url('admin').'">Admin</a></li>
        	<li><a href="cms">CMS</a></li>
            <li><a href="contact">Contact</a></li>
            <li><a href="debtor">Debtor</a></li>
            <li><a href="newsletter">Newsletter</a></li>
            <li><a href="onlinepayment">Onlinepayment</a></li>
            <li><a href="shop">Shop</a></li>
        </ul>

        ';
    }
}