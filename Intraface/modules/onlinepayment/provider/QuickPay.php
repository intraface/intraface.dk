<?php
/**
 * Onlinebetalingsklasse som specifik passer til Quickpay
 *
 *	@author		Sune Jensen
 * @author		Lars Olesen <lars@legestue.net>
 * @version	1.0
 *
 * @todo		Skal statuskoderne fra den oprindelige quickpayklasse oversættes
 *				til vores statuskoder?
 */

 require('Payment/Quickpay.php');

class OnlinePaymentQuickPay extends OnlinePayment {

    // This should maybe instead be: $transaction_status_types = array(
    var $statuskoder = array(
        '' => 'Ingen kontakt til Quickpay - mangler $eval',
        '000' => 'Godkendt',
        '001' => 'Afvist af PBS',
        '002' => 'Kommunikationsfejl',
        '003' => 'Kort udløbet',
        '004' => 'Status er forkert (Ikke autoriseret)',
        '005' => 'Autorisation er forældet',
        '006' => 'Fejl hos PBS',
        '007' => 'Fejl hos QuickPay',
        '008' => 'Fejl i parameter sendt til QuickPay'
    );

    var $quickpay;
    var $settings;
    var $eval;
    var $posc = 'K00500K00130';

    var $msg_types = array(
        '1100' => 'authorize', // tjekker
        '1220' => 'capture', // hæver
        'credit' => 'credit', // tilbagebetaler
        '1420' => 'reversal', // ophæver reservationen
        'status' => 'status' // ophæver reservationen

    );

    function OnlinePaymentQuickPay(&$kernel, $id) {
        OnlinePayment::OnlinePayment($kernel, $id);

        // hente settings om quickpay fra settingssystemet
        $this->settings = $this->getSettings();
        // åbne et quickpay objeckt
        $this->quickpay = new quickpay;
        $this->quickpay->set_md5checkword($this->settings['md5_secret']);
        $this->quickpay->set_merchant($this->settings['merchant_id']);
        $this->quickpay->set_curl_certificate(dirname(__FILE__) . '/../../../certificates/cacert.pem');

        // hvordan bliver disse sat - og hvad er det nøjagtigt?
        $this->quickpay->set_posc($this->posc);

    }
    /*
     * Så vidt jeg kan gennemskue skal disse være ens for de forskellige onlinebetalinger

    function getTransactionActions() {
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'Hæv'),
            1 => array(
                'action' => 'reverse',
                'label' => 'Tilbagebetal')
        );
    }
    */

    /**
     * Denne funktion behøves ikke, for i første omgang i hvert fald sker
     * autorisationen af dankort mv. uden for systemet. Vi skal kun kunne hæve og reverse fra
     * systemet
     *
    function authorize($cardnumber, $expirationdate, $cvd, $ordernum, $amount) {
        $this->quickpay->set_cardnumber($cardnumber);
        $this->quickpay->set_expirationdate($expirationdate); // YYMM
        $this->quickpay->set_cvd($cvd);
        $this->quickpay->set_ordernum($ordernum); // MUST at least be of length 4
        $this->quickpay->set_amount($amount); // skal være i ører
        $this->quickpay->set_currency('DKK');

        $this->eval = $this->quickpay->authorize();

        // der skal laves noget if - then - og tingene skal gemmes på en eller anden måde

        return $this->eval;
    }
    **/

    function transactionAction($action) {

        $this->quickpay->set_msgtype(array_search($action, $this->msg_types));

        if($action == "capture") {

            // Her kan der laves en capture fra QuickPay;

            // henter beløbene fra denne onlinebetaling
            $this->quickpay->set_transaction($this->get('transaction_number'));
            $this->quickpay->set_amount(number_format($this->get('amount'), 0) * 100);

            $this->eval = $this->quickpay->capture();

            if (!empty($this->eval['qpstat']) AND $this->eval['qpstat'] === '000') {
                // success

                if($this->addAsPayment()) {
                    $this->setStatus("captured");
                }
                else {
                    trigger_error("Onlinebetalingen kunne ikke overføres til fakturaen", FATAL);
                }

                return 1;

            }
            else {
                // fiasko
                $this->error->set('Vi kunne ikke capture betalingen');
                return 0;
            }


        }
        elseif($action == "reverse") {
            die('not implemented');
            /*
            $this->quickpay->set_transaction($transaction);
            $this->eval = $this->quickpay->reverse();

            if (!empty($this->eval['qpstat']) AND $eval['this->qpstat'] === '000') {
                $this->setStatus("reversed");
                return 1;

            }
            else {
                $this->error->set('Kunne ikke reverse betalingen');
                return 0;

            }
            */

        }
        else {
            trigger_error("Ugyldig handling i Quickpay->transactionAction()", ERROR);
        }
    }

    function addCustomVar($var, $value) {
        $this->quickpay->add_customVars($var, $value);
    }

    function setSettings($input) {
        $this->kernel->setting->set('intranet', 'onlinepayment.quickpay.md5_secret', $input['md5_secret']);
        $this->kernel->setting->set('intranet', 'onlinepayment.quickpay.merchant_id', $input['merchant_id']);
        return 1;
    }

    function getSettings() {
        $this->value['md5_secret'] = 	$this->kernel->setting->get('intranet', 'onlinepayment.quickpay.md5_secret');
        $this->value['merchant_id'] = 	$this->kernel->setting->get('intranet', 'onlinepayment.quickpay.merchant_id');
        return $this->value;
    }

    function isSettingsSet() {
        if ($this->kernel->setting->get('intranet', 'onlinepayment.quickpay.md5_secret') AND $this->kernel->setting->get('intranet', 'onlinepayment.quickpay.merchant_id')) {
            return 1;
        }
        return 0;
    }

}
?>