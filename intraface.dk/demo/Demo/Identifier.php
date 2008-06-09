<?php
class Demo_Identifier extends k_Controller
{
    public $map = array(
        'shop' => 'Demo_Shop_Root',
        'cms' => 'Demo_CMS_Root',
        'login' => 'Demo_Login_Root'
    );

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }
    
    function getPrivateKey()
    {
        $client = $this->registry->get('admin');

        try {
            $this->private_key = $client->getPrivateKey($this->name);
        } catch (Exception $e) {
            throw $e;
        }

        if (empty($this->private_key)) {
            throw new Exception('private key is not found for the intranet - shop cannot be generated');
        }

        return $this->private_key;        
    }

    function forward($name)
    {
        if ($name == 'shop') {
            $next = new Demo_Shop_Root($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'cms') {
            $next = new Demo_CMS_Root($this, $name);
            return $next->handleRequest();
        } elseif ($name == 'login') {
            $next = new Demo_Login_Root($this, $name);
            return $next->handleRequest();
        }
    }
}