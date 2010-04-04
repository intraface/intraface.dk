<?php
class Intraface_Controller_Payment
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        if (!empty($_GET['language']) && $_GET['language'] == 'da') {
            $text[0] = 'Intraface Betaling';
            $text[1] = 'Du er nu ved at betale for ordre nummer';
            $text[2] = 'I alt hæves %s på fra dit kort';
            $text[3] = 'Betalingen foretages over Quickpay\'s sikker betalingsserver.';

        } else {
            $text[0] = 'Intraface Payment';
            $text[1] = 'You are now about to pay for order number';
            $text[2] = '%s is withdrawed from your card';
            $text[3] = 'The payment is carried out via Quickpay\'s secure server.';
        }

        $this->document->setTitle('Payment');

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/payment');
        $content = $tpl-render($this, array('text' => $text));
        return new k_HttpResponse(200, $content);
    }
}