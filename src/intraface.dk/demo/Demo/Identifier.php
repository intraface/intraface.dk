<?php
class Demo_Identifier extends k_Component
{
    private $map = array(
        'shop' => 'Demo_Shop_Root',
        'cms' => 'Demo_CMS_Root',
        'login' => 'Demo_Login_Root',
        'newsletter' => 'Demo_Newsletter_Root'
    );

    private $private_key;
    private $client;

    function __construct(IntrafacePublic_Admin_Client_XMLRPC $client)
    {
        $this->client = $client;
    }

    function map($name)
    {
        return $this->map[$name];
    }

    function renderHtml()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function getPrivateKey()
    {
        if (!empty($this->private_key)) {
            return $this->private_key;
        }

        try {
            return $this->private_key = $this->client->getPrivateKey($this->name());
        } catch (Exception $e) {
            throw $e;
        }

        if (empty($this->private_key)) {
            throw new Exception('private key is not found for the intranet - shop cannot be generated');
        }

        return $this->private_key;
    }
}