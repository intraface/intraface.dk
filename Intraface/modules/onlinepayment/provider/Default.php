<?php
/**
 * Onlinebetalingsklasse som er generel, hvis man ikke har nogen udbyder
 * @package Intraface_OnlinePayment
 * @author		Lars Olesen <lars@legestue.net>
 * @version	1.0
 */

class OnlinePaymentDefault extends OnlinePayment {


    function OnlinePaymentDefault(&$kernel, $id) {

        OnlinePayment::OnlinePayment($kernel, $id);
    }
    /*
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
    function transactionAction($action) {

        if($action == "capture") {

            // Her kan der laves en capture fra QuickPay;

            if($this->addAsPayment()) {
                $this->setStatus("captured");
            }
            else {
                trigger_error("Onlinebetalingen kunne ikke overføres til fakturaen", FATAL);
            }
        }
        elseif($action == "reverse") {

            // her skal reverse så laves?

            $this->setStatus("reversed");
            return 1;
        }
        else {
            trigger_error("Ugyldig handling i Quickpay->transactionAction()", ERROR);
        }
    }

    function setSettings() {
        // void
    }

    function getSettings() {
        // void
    }

    function isSettingsSet() {
        return 1;
    }

}
?>