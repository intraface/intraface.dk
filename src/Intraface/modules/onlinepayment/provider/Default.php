<?php
/**
 * Onlinebetalingsklasse som er generel, hvis man ikke har nogen udbyder
 * @package Intraface_OnlinePayment
 * @author		Lars Olesen <lars@legestue.net>
 * @version	1.0
 */

class OnlinePaymentDefault extends OnlinePayment
{
    function __construct($kernel, $id) {

        parent::__construct($kernel, $id);
    }

    function getTransactionActions()
    {
        return array(
            0 => array(
                'action' => 'capture',
                'label' => 'Withdrawed'),
            1 => array(
                'action' => 'reverse',
                'label' => 'Paid back')
        );
    }

    function transactionAction($action)
    {

        if ($action == "capture") {

            // Her kan der laves en capture fra Betalingsudbyder;

            if ($this->addAsPayment()) {
                $this->setStatus("captured");
                return true;
            } else {
                trigger_error("Onlinebetalingen kunne ikke overføres til fakturaen", E_USER_ERROR);
                return false;
            }
        } elseif ($action == "reverse") {

            // her skal reverse så laves?

            $this->setStatus("reversed");
            return true;
        } else {
            trigger_error("Ugyldig handling i Onlinepayment_Provider_Default->transactionAction()", E_USER_ERROR);
            return false;
        }
    }

    function setSettings()
    {
        // void
    }

    function getSettings()
    {
        // void
    }

    function isSettingsSet()
    {
        return 1;
    }
}