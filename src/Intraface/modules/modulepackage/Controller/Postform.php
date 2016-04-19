<?php
class Intraface_modules_modulepackage_Controller_PostForm extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function postForm()
    {
        $module = $this->getKernel()->module('modulepackage');

        $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
        $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
        $language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';

        if ($this->body('pay')) {
            $process = $payment_authorize->getPaymentProcess();
            $url = $process->process($this->body(), $_SESSION);
            return new k_SeeOther($url);
        }

        $form = $payment_authorize->getForm(
            $this->body('order_id'),
            $this->body('amount'),
            $this->body('currency'),
            $language,
            NET_SCHEME . NET_HOST. $this->url('../', array('status' => 'success')),
            NET_SCHEME . NET_HOST. $this->url('../payment', array('action_store_identifier' => $this->body('action_store_identifier'), 'payment_error' => 'true')),
            NET_SCHEME . NET_HOST. $this->url('/process', array('action_store_identifier' => $this->body('action_store_identifier'))),
            $this->query(),
            $this->body()
        );

        $url = $form->getAction();

        if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
            $form_action = '';
        } else {
            $form_action = $url;
        }

        $data = array(
            'form' => $form,
            'form_action' => $form_action,
            'language' => $language);

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/postform');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
