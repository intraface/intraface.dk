<?php
/**
 * Onlinebetalingsklasse som specifik passer til Quickpay
 *
 * @package Intraface_OnlinePayment
 * @author      Sune Jensen
 * @author      Lars Olesen <lars@legestue.net>
 * @version     1.0
 *
 * @todo        Skal statuskoderne fra den oprindelige quickpayklasse oversættes
 *              til vores statuskoder?
 */
class OnlinePaymentQuickPay extends OnlinePayment
{
    // This should maybe instead be: $transaction_status_types = array(
    public $statuskoder = array(
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

    public $quickpay;
    public $settings;
    public $eval;
    public $posc = 'K00500K00130';

    public $msg_types = array(
        '1100' => 'authorize', // tjekker
        '1220' => 'capture', // hæver
        'credit' => 'credit', // tilbagebetaler
        '1420' => 'reversal', // ophæver reservationen
        'status' => 'status'
    );
    protected $dbquery;

    /**
     * Constructor
     *
     * @param object  $kernel Kernel object
     * @param integer $id     Id for the payment
     *
     * @return void
     */
    function __construct($kernel, $id)
    {
        parent::__construct($kernel, $id);

        // hente settings om quickpay fra settingssystemet
        $this->settings = $this->getSettings();
        // �bne et quickpay objeckt
        $this->quickpay = new quickpay;
        $this->quickpay->set_md5checkword($this->settings['md5_secret']);
        $this->quickpay->set_merchant($this->settings['merchant_id']);
        $this->quickpay->set_curl_certificate(dirname(__FILE__) . '/../../../certificates/cacert.pem');

        // hvordan bliver disse sat - og hvad er det n�jagtigt?
        $this->quickpay->set_posc($this->posc);
    }

    /**
     * Denne funktion beh�ves ikke, for i f�rste omgang i hvert fald sker
     * autorisationen af dankort mv. uden for systemet. Vi skal kun kunne h�ve og reverse fra
     * systemet
     *
    function authorize($cardnumber, $expirationdate, $cvd, $ordernum, $amount) {
        $this->quickpay->set_cardnumber($cardnumber);
        $this->quickpay->set_expirationdate($expirationdate); // YYMM
        $this->quickpay->set_cvd($cvd);
        $this->quickpay->set_ordernum($ordernum); // MUST at least be of length 4
        $this->quickpay->set_amount($amount); // skal v�re i �rer
        $this->quickpay->set_currency('DKK');

        $this->eval = $this->quickpay->authorize();

        // der skal laves noget if - then - og tingene skal gemmes p� en eller anden m�de

        return $this->eval;
    }
    **/

    /**
     * @todo does what?
     *
     * @todo what about splitting up to smaller functions?
     *
     * @param string $action Which action to perform?
     *
     * @return void
     */
    function transactionAction($action)
    {
        $this->quickpay->set_msgtype(array_search($action, $this->msg_types));

        if ($action == "capture") {
            // Her kan der laves en capture fra QuickPay;

            // henter bel�bene fra denne onlinebetaling
            $this->quickpay->set_transaction($this->get('transaction_number'));
            $this->quickpay->set_amount(round($this->get('amount') * 100));

            $this->eval = $this->quickpay->capture();

            if (!empty($this->eval['qpstat']) and $this->eval['qpstat'] === '000') {
                // success

                if ($this->addAsPayment()) {
                    $this->setStatus("captured");
                } else {
                    throw new Exception('Onlinebetalingen kunne ikke overf�res til fakturaen i Quickpay->transactionAction()');
                }

                return true;
            } else {
                // fiasko
                $this->error->set('Betalingen kunne ikke h�ves: '.$this->eval['qpstatmsg']);
                return false;
            }
        } elseif ($action == "reversal") {
            $this->quickpay->set_transaction($this->get('transaction_number'));
            $this->eval = $this->quickpay->reverse();
            if (!empty($this->eval['qpstat']) and $this->eval['qpstat'] === '000') {
                $this->setStatus("reversed");
                return true;
            } else {
                $this->error->set('Betalingen kunne ikke tilbagebetales: ' . $this->eval['qpstatmsg']);
                return false;
            }
        } else {
            throw new Exception('Ugyldig handling i Quickpay->transactionAction()');
        }
    }

    function getTransactionActions()
    {
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'Hæv'),
            1 => array(
                'action' => 'reversal',
                'label' => 'Tilbagebetal')
        );
    }

    /**
     * Adds a custom var
     *
     * @param string $var   The var to add
     * @param string $value The value for the var
     *
     * @return void
     */
    function addCustomVar($var, $value)
    {
        $this->quickpay->add_customVars($var, $value);
    }

    /**
     * Adds a custom var
     *
     * @param array $input @todo what
     *
     * @return boolean
     */
    function setSettings($input)
    {
        $this->kernel->setting->set('intranet', 'onlinepayment.quickpay.md5_secret', $input['md5_secret']);
        $this->kernel->setting->set('intranet', 'onlinepayment.quickpay.merchant_id', $input['merchant_id']);
        return true;
    }

    /**
     * Gets a setting
     *
     * @return string
     */
    function getSettings()
    {
        $this->value['md5_secret'] =    $this->kernel->setting->get('intranet', 'onlinepayment.quickpay.md5_secret');
        $this->value['merchant_id'] =   $this->kernel->setting->get('intranet', 'onlinepayment.quickpay.merchant_id');
        return $this->value;
    }

    /**
     * Returns whether a setting is set
     *
     * @return boolean
     */
    function isSettingsSet()
    {
        if ($this->kernel->setting->get('intranet', 'onlinepayment.quickpay.md5_secret') and $this->kernel->setting->get('intranet', 'onlinepayment.quickpay.merchant_id')) {
            return true;
        }
        return false;
    }
}
