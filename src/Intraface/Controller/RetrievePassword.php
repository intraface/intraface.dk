<?php
/**
 * Logout
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_RetrievePassword extends k_Component
{
    public $msg;

    function execute()
    {
        $this->url_state->init("continue", $this->url('/login', array('flare' => 'Vi har sendt en e-mail til dig med en ny adgangskode, som du b�r g� ind og lave om med det samme.')));
        return parent::execute();
    }

    function renderHtml()
    {
        $this->document->setTitle('Retrieve forgotten password');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/retrievepassword.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
    	if (!Intraface_User::sendForgottenPasswordEmail($this->body('email'))) {
    	    $this->msg = '<p>Det gik <strong>ikke</strong> godt. E-mailen kunne ikke sendes. Du kan pr�ve igen senere.</p>';
    	    return $this->render();
    	}
    	return new k_SeeOther($this->query('continue'));
    }
}