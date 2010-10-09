<?php
class Demo_Newsletter_Show extends k_Component
{
    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'IntrafacePublic_newsletter_Controller_Index';
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url($this->name()));
    }

    function forward($class_name, $namespace = '')
    {
        $next = new IntrafacePublic_Newsletter_Controller_Index($this->getNewsletter(), $this->template);
        $next->setContext($this);
        $next->setUrlState($this->url_state);
        $next->setDocument($this->document);
        $next->setComponentCreator($this->component_creator);
        //$next->setTranslatorLoader($this->translator_loader);
        $next->setDebugger($this->debugger);

        return $next->dispatch();
    }

    private function getCredentials()
    {
        return array("private_key" => $this->context->getPrivateKey(),
                     "session_id" => md5($this->session()->sessionId()));
    }

    function getNewsletter()
    {
        $debug = false;
        $list_id = $this->name();
        $client = new IntrafacePublic_Newsletter_Client_XMLRPC(
            $this->getCredentials(),
            $list_id,
            $debug,
            INTRAFACE_XMLPRC_SERVER_PATH . "newsletter/server0101.php",
            'utf-8'); // , 'iso-8859-1', 'xmlrpcext'
        return $client;
    }
}