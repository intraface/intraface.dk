<?php
class Intraface_Tools_Controller_Login extends k_Controller
{
    
    
    public function GET()
    {
        
        return $this->render('Intraface/Tools/templates/login-tpl.php');
    }
    
    public function POST()
    {
        
        if($this->registry->get('user')->login($this->POST['username'], $this->POST['password'])) {
            throw new k_http_Redirect($this->url('../'));
        }
        else {
            return $this->render('Intraface/Tools/templates/login.php', array('error_message' => 'Invalid credentials'));
        }
    }
}