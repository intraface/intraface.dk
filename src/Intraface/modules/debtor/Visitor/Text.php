<?php
class Debtor_Report_Text
{
    function output($debtor)
    {
        $table = new Console_Table;
        foreach ($debtor->getItems() as $item) {
            if ($debtor->getCurrency()) {
                $amount = $item["amount_currency"]->getAsLocal('da_dk', 2);
                $currency_iso_code = $debtor->getCurrency()->getType()->getIsoCode();
            } else {
                $amount = $item["amount"]->getAsLocal('da_dk', 2);
                $currency_iso_code = 'DKK';
            }

            $table->addRow(array(round($item["quantity"]), substr($item["name"], 0, 40), $currency_iso_code.' ' . $amount));
        }
        return $table->getTable();
    }
}
