<?php
class Intraface_modules_newsletter_Controller_Subscriber extends k_Component
{
    function renderHtml()
    {
        if ($this->query('remind') == 'true') {
            $subscriber = new NewsletterSubscriber($this->context->getList(), intval($this->name()));
            if (!$subscriber->sendOptInEmail()) {
            	throw new Exception('Could not send the optin e-mail');
            }
            return new k_SeeOther($this->url(null, array('flare' => 'Reminder e-mail sent')));
        }

        return '<h1>'.$this->t('Subscriber').'</h1><p><a href="'.$this->url(null, array('remove')).'">Remove contact from list</a></p>';
    }

    function renderHtmlRemove()
    {
        $module = $this->context->getKernel()->module('newsletter');

        $subscriber = new NewsletterSubscriber($this->context->getList(), $this->name());
        $subscriber->delete();
        return new k_SeeOther($this->url('../', array('use_stored' => 'true')));
    }
}