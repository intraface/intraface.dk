<?php
/**
 * Onlinebetalingsklasse som specifik passer til DanDomain
 * @package Intraface_OnlinePayment
 *  @author         Sune Jensen
 * @author      Lars Olesen <lars@legestue.net>
 * @version     1.0
 *
 */
class OnlinePaymentDanDomain extends OnlinePayment
{

    public $transaction_status_types = array(
        '-1' => 'Godkendt',
        '' => 'Ingen kontakt til Dandomin',
        '0' => 'Forretningsnummer ugyldigt',
        '1' => 'Ugyldigt kreditkortnummer',
        '2' => 'Ugyldigt beløb',
        '3' => 'OrderID mangler eller er ugyldig',
        '4' => 'PBS afvisning',
        '5' => 'Intern server fejl hos DanDomain eller PBS',
        '6' => 'E-dankort ikke tilladt',
        '7' => 'ewire ikke tilladt',
        '8' => '3-D Secure ikke tilladt',
        '9' => 'ExpireMonth/ExpireYear Ugyldig.',
        '10' => 'Ugyldig kreditkort type',
        '11' => 'Ugyldig Checksum',
        '12' => 'Instant Capture failed',
        '13' => 'Recurring payments not allowed',
        '14' => 'OrderID must be unique within same date',
        '15' => 'Customer number for recurring payment must be unique'
    );

    public $transaction_status_authorized = "-1";

    public $settings;

    /*
    var $msg_types = array(
        '1100' => 'authorize', // tjekker
        '1220' => 'capture', // hæver
        'credit' => 'credit', // tilbagebetaler
        '1420' => 'reversal', // ophæver reservationen
        'status' => 'status' // ophæver reservationen

    );
    */

    function __construct($kernel, $id)
    {
        parent::__construct($kernel, $id);

        // hente settings om DanDomain fra settingssystemet
        $this->settings = $this->getSettings();
    }

    function getTransactionActions()
    {
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'Hæv'),
            1 => array(
                'action' => 'reverse',
                'label' => 'Tilbagebetal')
        );
    }

    function transactionAction($action)
    {

        require_once "HTTP/Request.php";
        $http_request = new HTTP_Request("");

        $basis_url = 'https://pay.dandomain.dk/PayApi.asp?username='.$this->settings['merchant_id'].'&password='.$this->settings['password'].'&Transid='.$this->get('transaction_number');


        if ($action == "capture") {
            if ($this->get('amount') < $this->get('original_amount')) {
                $add_url = '&ChangeAmount=1&amount='.number_format($this->get('amount'), 2, ",", '');
            } else {
                $add_url = '';
            }


            $http_request->setURL($basis_url.$add_url.'&Capture=1');
            $result = $http_request->sendRequest();
            if (PEAR::isError($result)) {
                throw new Exception('Error in sending request: '.$result->getMessage().' '.$result->getUserInfo());
            }

            if ($http_request->getResponseCode() != '200') {
                throw new Exception("DanDomain serveren er nede, eller fejl i capture adresse", E_USER_WARNING);
                exit;
            }

            if (substr(trim($http_request->getResponseBody()), 0, 11) == 'Transaktion') { // hmm ikke helt godt, men vel ok. Response er "Transanktion #1111111 er h�vet"

                if ($this->addAsPayment()) {
                    $this->setStatus("captured");
                } else {
                    throw new Exception("Onlinebetalingen er hævet, men kunne ikke overføres som betaling til fakturaen");
                }
                return 1;
            } else {
                // fiasko
                $this->error->set('Vi kunne ikke hæve betalingen, vi fik følgende fejl: '.utf8_encode($http_request->getResponseBody()));
                return 0;
            }
        } elseif ($action == "reverse") {
            $http_request->setURL($basis_url.'&Reject=1');
            $result = $http_request->sendRequest();
            if (PEAR::isError($result)) {
                throw new Exception('Error in sending request: '.$result->getMessage().' '.$result->getUserInfo());
            }

            if ($http_request->getResponseCode() != '200') {
                throw new Exception("DanDomain serveren er nede, eller fejl i capture adresse", E_USER_WARNING);
                exit;
            }

            if (substr($http_request->getResponseBody(), 0, 3) == '200') {
                $this->setStatus("reversed");
                return 1;
            } else {
                // fiasko
                $this->error->set('Vi kunne ikke tilbagebetale betalingen, vi fik følgende fejl: '.$http_request->getResponseBody());
                return 0;
            }
        } else {
            throw new Exception("Ugyldig handling i Dandomain->transactionAction()");
        }
    }

    function setSettings($input)
    {
        $this->kernel->setting->set('intranet', 'onlinepayment.dandomain.password', $input['password']);
        $this->kernel->setting->set('intranet', 'onlinepayment.dandomain.merchant_id', $input['merchant_id']);
        return 1;
    }

    function getSettings()
    {
        $this->value['password'] =  $this->kernel->setting->get('intranet', 'onlinepayment.dandomain.password');
        $this->value['merchant_id'] =   $this->kernel->setting->get('intranet', 'onlinepayment.dandomain.merchant_id');
        return $this->value;
    }

    function isSettingsSet()
    {
        if ($this->kernel->setting->get('intranet', 'onlinepayment.dandomain.password') and $this->kernel->setting->get('intranet', 'onlinepayment.dandomain.merchant_id')) {
            return 1;
        }
        return 0;
    }
}
