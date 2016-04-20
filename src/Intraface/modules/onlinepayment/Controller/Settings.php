<?php
class Intraface_modules_onlinepayment_Controller_Settings extends k_Component
{
    protected $template;
    protected $doctrine;

    function __construct(k_TemplateFactory $template, Doctrine_Connection_Common $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->template = $template;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function map($name)
    {
        return 'Intraface_modules_onlinepayment_Controller_ChooseProvider';
    }

    function renderHtml()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');
        $translation = $this->context->getKernel()->getTranslation('onlinepayment');
        $implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

        $onlinepayment = OnlinePayment::factory($this->context->getKernel());
        $language = new Intraface_modules_language_Languages;
        // @todo der skal laves en gateway, der bruger dql.
        $settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findOneByIntranetId($this->context->getKernel()->intranet->getId());

        if (!$settings) {
            $settings = new Intraface_modules_onlinepayment_Language;
            $settings->save();
        }
        $value = $onlinepayment->getSettings();

        $data = array(
            'kernel' => $this->getKernel(),
            'implemented_providers' => $implemented_providers,
            'settings' => $settings,
            'language' => $language,
            'value' => $value
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/settings');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $onlinepayment_module = $this->context->getKernel()->module('onlinepayment');
        $translation = $this->context->getKernel()->getTranslation('onlinepayment');
        $implemented_providers = $onlinepayment_module->getSetting('implemented_providers');

        $onlinepayment = OnlinePayment::factory($this->context->getKernel());
        $language = new Intraface_modules_language_Languages;
        /**
         * @todo: Should be don with gateway instead!
         */
        $settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findOneByIntranetId($this->context->getKernel()->intranet->getId());
        if (!$settings) {
            $settings = new Intraface_modules_onlinepayment_Language;
        }
        $settings->Translation['da']->email = $_POST['email']['da'];
        $settings->Translation['da']->subject = $_POST['subject']['da'];
        
        foreach ($language->getChosenAsArray() as $lang) {
            $settings->Translation[$lang->getIsoCode()]->email = $_POST['email'][$lang->getIsoCode()];
            $settings->Translation[$lang->getIsoCode()]->subject = $_POST['subject'][$lang->getIsoCode()];
        }

        $settings->save();

        if ($onlinepayment->setSettings($_POST)) {
            return new k_SeeOther($this->url());
        } else {
            $value = $_POST;
        }
        return $this->render();
    }
}
