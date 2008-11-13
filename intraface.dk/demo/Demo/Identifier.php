<?php
class Demo_Identifier extends k_Controller
{
    public $map = array(
        'shop' => 'Demo_Shop_Root',
        'cms' => 'Demo_CMS_Root',
        'login' => 'Demo_Login_Root'
    );
    
    private $private_key;

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }
    
    function getPrivateKey()
    {
        if (!empty($this->private_key)) {
            return $this->private_key;
        }
        
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
        
        // If we are going to either shop (and when implemented, login) we see if onlinepayment is present
        if ($name == 'shop') { /* OR name == 'login */
            
            $credentials = array('private_key' => $this->getPrivateKey(), "session_id" => md5($this->registry->get("k_http_Session")->getSessionId()));
            
            if ($this->registry->get('admin')->hasModuleAccess($credentials, 'onlinepayment')) {
                
                $this->registry->registerConstructor('onlinepayment', create_function(
                    '$className, $args, $registry',  
                    'return new IntrafacePublic_OnlinePayment(' .
                    '    new IntrafacePublic_OnlinePayment_Client_XMLRPC(' .
                    '        array("private_key" => "'.$this->getPrivateKey().'", "session_id" => md5($registry->get("k_http_Session")->getSessionId())), ' .
                    '        false, ' .
                    '        INTRAFACE_XMLPRC_SERVER_PATH . "onlinepayment/server0002.php"' .
                    '    ),' .
                    '    $registry->get("cache")' .
                    ');'
                ));
                
                
                $this->registry->registerConstructor('onlinepayment:payment_html', create_function(
                    '$className, $args, $registry',  
                    'return new Ilib_Payment_Html("FakeQuickpay", "12345678", "fakequickpaymd5secret", $registry->get("k_http_Session")->getSessionId());'
                ));
            }
        }
        
        
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