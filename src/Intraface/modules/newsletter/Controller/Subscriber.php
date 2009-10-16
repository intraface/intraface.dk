<?php
class Intraface_modules_newsletter_Controller_Subscriber extends k_Component
{
    function renderHtmlRemove()
    {
        $module = $this->context->getKernel()->module('newsletter');

        $subscriber = new NewsletterSubscriber($this->context->getList(), $this->name());
        $subscriber->delete();
        return new k_SeeOther($this->url('../', array('use_stored' => 'true')));
    }

    function renderHtml()
    {
        return '<a href="'.$this->url(null, array('remove')).'">Remove contact from list</a>';
    }
}