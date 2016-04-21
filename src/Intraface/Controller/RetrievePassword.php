<?php
/**
 * Retrieve password
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_RetrievePassword extends k_Component
{
    public $msg;
    protected $template;
    protected $email;
    protected $mailer;

    function __construct(k_TemplateFactory $template, Swift_Message $email, Swift_Mailer $mailer)
    {
        $this->template = $template;
        $this->email = $email;
        $this->mailer = $mailer;
    }

    function execute()
    {
        $this->url_state->init("continue", $this->url('/login', array('flare' => 'Vi har sendt en e-mail til dig med en ny adgangskode, som du bør gå ind og lave om med det samme.')));
        return parent::execute();
    }

    function renderHtml()
    {
        $this->document->setTitle('Retrieve forgotten password');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/retrievepassword');
        return $smarty->render($this);
    }

    function postForm()
    {
        $new_password = Intraface_User::generateNewPassword($this->body('email'));

        $body  = "Huha, det var heldigt, at vi stod på spring i kulissen, så vi kan hjælpe dig med at lave en ny adgangskode.\n\n";
        $body .= "Din nye adgangskode er: " . $new_password . "\n\n";
        $body .= "Du kan logge ind fra:\n\n";
        $body .= "<".PATH_WWW . 'login'.">\n\n";
        $body .= "Med venlig hilsen\nDin hengivne webserver";

        $this->email
            ->setSubject('Tsk, glemt din adgangskode?')
            ->setFrom(array('robot@intraface.dk' => 'Intraface.dk'))
            ->setTo(array($this->body('email')))
            ->setBody($body);

        if (!$this->mailer->send($this->email)) {
            $this->msg = '<p>Det gik <strong>ikke</strong> godt. E-mailen kunne ikke sendes. Du kan prøve igen senere.</p>';
            return $this->render();
        }
        return new k_SeeOther($this->query('continue'));
    }
}
