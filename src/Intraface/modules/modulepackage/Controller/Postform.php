<?php
class Intraface_modules_modulepackage_Controller_PostForm extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('modulepackage');

        $translation = $this->getKernel()->getTranslation('modulepackage');

        $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
        $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
        $language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';

        $form = $payment_authorize->getForm(
            $_POST['order_id'],
            $_POST['amount'],
            $_POST['currency'],
            $language,
            NET_SCHEME.NET_HOST. $this->url('../', array('status' => 'success')),
            NET_SCHEME.NET_HOST. $this->url('../payment', array('action_store_identifier' => $_POST['action_store_identifier'], 'payment_error' => 'true')),
            NET_SCHEME.NET_HOST. $this->url('/process', array('action_store_identifier' => $_POST['action_store_identifier'])),
            $_GET,
            $_POST
        );

        $data = array(
        	// 'modulepackagemanager' => $modulepackagemanager,
            'form' => $form,
            'language' => $language);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/postform');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $module = $this->getKernel()->module('modulepackage');

        $translation = $this->getKernel()->getTranslation('modulepackage');

        $payment_provider = 'Ilib_Payment_Authorize_Provider_'.INTRAFACE_ONLINEPAYMENT_PROVIDER;
        $payment_authorize = new $payment_provider(INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET);
        $language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';

        if (!empty($_POST['pay'])) {
            $process = $payment_authorize->getPaymentProcess();
            $url = $process->process($_POST, $_SESSION);

            return new k_SeeOther($url);
        }
        return $this->render();
    }
}
