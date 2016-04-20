<?php
class Intraface_shared_email_Signature
{
    /**
     * @var object Intraface_User
     */
    private $user;
    
    /**
     *
     * @var object Intraface_Setting
     */
    private $setting;
    
    /**
     *
     * @var object Intraface_Intranet
     */
    private $intranet;
    
    /**
     * Constructor
     *
     * @param object $user Intraface_User
     * @param object $setting Intraface_Setting
     * @return void
     */
    public function __construct($user, $intranet, $setting)
    {
        if (!is_object($user)) {
            throw new Exception('First parameter must be object Intraface_User');
        }
        
        if (!is_object($setting)) {
            throw new Exception('Second parameter must be object Intraface_Intranet');
        }
        
        if (!is_object($setting)) {
            throw new Exception('Third parameter must be object Intraface_Setting');
        }
        
        $this->user = $user;
        $this->intranet = $intranet;
        $this->setting = $setting;
    }
    
    /**
     *
     * @return object Intraface_Setting
     */
    private function getSetting()
    {
        return $this->setting;
    }
    
    /**
     *
     * @return object Intraface_User
     */
    private function getUser()
    {
        return $this->user;
    }
    
    /**
     *
     * @return object Intraface_Intranet
     */
    private function getIntranet()
    {
        return $this->intranet;
    }
    
    /**
     * Returns signature as text
     * @return string with signature
     */
    public function getAsText()
    {
        switch ($this->getSetting()->get('user', 'email.signature_type')) {
            case 0:
                return '';
                break;
            case 1:
            default:
                return "--\n" . $this->getUser()->getAddress()->get('name') . "\n" . $this->getIntranet()->get('name');
                break;
            case 2:
                return $this->getSetting()->get('user', 'email.custom_signature');
                break;
        }
        
        throw new Exception('Invalid signature type "'.$this->getSetting()->get('user', 'email.signature_type').'"');
    }
}
