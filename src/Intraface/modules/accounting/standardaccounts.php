<?php

/**
 * Standard accounts
 *
 * The accounts are similar to the accounts at SummaSummarum.
 * @package Intraface_Accounting
 * @author Lars Olesen <lars@legestue.net>
 * @version 1.0
 */

$i = 0; // integer til automatisk tælling

/**********************************************/

$standardaccounts[$i]['number']	= '1000';
$standardaccounts[$i]['name']			= 'Indtægter';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['result_account_id_start']			= true;


$i++;

$standardaccounts[$i]['number']	= '1110';
$standardaccounts[$i]['name']			= 'Salg med moms';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '2';
$standardaccounts[$i]['vat_key']	= '2';

$i++;

$standardaccounts[$i]['number']	= '1120';
$standardaccounts[$i]['name']			= 'Salg uden moms';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '2';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '1990';
$standardaccounts[$i]['name']			= 'Nettoomsætning';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '2';
$standardaccounts[$i]['sum_to']	= '1989';
$standardaccounts[$i]['use_key']			= '1';

$i++;

/**********************************************/

$standardaccounts[$i]['number']	= '2000';
$standardaccounts[$i]['name']			= 'Udgifter';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '2100';
$standardaccounts[$i]['name']			= 'Vareforbrug fra varelager';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '4100';
$standardaccounts[$i]['name']			= 'EU-varekøb';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['buy_eu']	= 1;

$i++;

$standardaccounts[$i]['number']	= '4700';
$standardaccounts[$i]['name']			= 'Varekøb uden for EU';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['buy_abroad']	= 1;

$i++;

$standardaccounts[$i]['number']	= '4990';
$standardaccounts[$i]['name']			= 'Dækningsbidrag';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '1';
$standardaccounts[$i]['sum_to']	= '4989';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '7000';
$standardaccounts[$i]['name']			= 'Kontorartikler';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['vat_key']	= '1';
$standardaccounts[$i]['use_key']			= '3';

$i++;

$standardaccounts[$i]['number']	= '7200';
$standardaccounts[$i]['name']			= 'Porto';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '7400';
$standardaccounts[$i]['name']			= 'Småanskaffelser';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';

$i++;

$standardaccounts[$i]['number']	= '7600';
$standardaccounts[$i]['name']			= 'Telefon';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';

$i++;

$standardaccounts[$i]['number']	= '7650';
$standardaccounts[$i]['name']			= 'Internet og webhotel';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';

$i++;

$standardaccounts[$i]['number']	= '7800';
$standardaccounts[$i]['name']			= 'Diverse incl. moms';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';

$i++;

$standardaccounts[$i]['number']	= '7900';
$standardaccounts[$i]['name']			= 'Diverse excl. moms';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';

$i++;


$standardaccounts[$i]['number']	= '39990';
$standardaccounts[$i]['name']			= 'Resultat før renter';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '1';
$standardaccounts[$i]['sum_to']	= '39989';
$standardaccounts[$i]['use_key']			= '1';

$i++;

/**********************************************/

$standardaccounts[$i]['number']	= '40000';
$standardaccounts[$i]['name']			= 'Finansieringsindtægter';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';


$i++;

$standardaccounts[$i]['number']	= '41000';
$standardaccounts[$i]['name']			= 'Renteindtægter';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '43000';
$standardaccounts[$i]['name']			= 'Finansieringsudgifter';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '43100';
$standardaccounts[$i]['name']			= 'Renteudgifter';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';

$i++;


$standardaccounts[$i]['number']	= '43850';
$standardaccounts[$i]['name']			= 'Bankgebyr';
$standardaccounts[$i]['type_key']			= '2';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';

$i++;

$standardaccounts[$i]['number']	= '43990';
$standardaccounts[$i]['name']			= 'Finansieringsudgifter';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '43000';
$standardaccounts[$i]['sum_to']	= '43995';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '49990';
$standardaccounts[$i]['name']			= 'Periodens resultat';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '1';
$standardaccounts[$i]['sum_to']	= '43989';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['result_account_id_end']			= true;


$i++;

/**********************************************/

$standardaccounts[$i]['number']	= '50000';
$standardaccounts[$i]['name']			= 'Balance';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['balance_account_id_start']			= true;

$i++;

/**********************************************/

$standardaccounts[$i]['number']	= '50010';
$standardaccounts[$i]['name']			= 'Aktiver';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;


$standardaccounts[$i]['number']	= '50800';
$standardaccounts[$i]['name']			= 'Driftssmidler';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '50810';
$standardaccounts[$i]['name']			= 'Tilgang i årets løb';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '50820';
$standardaccounts[$i]['name']			= 'Afgang i året';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '2';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '50830';
$standardaccounts[$i]['name']			= 'Akkumulerede afskrivning på driftssmidler';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;


$standardaccounts[$i]['number']	= '55100';
$standardaccounts[$i]['name']			= 'Varelager';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '1';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '56100';
$standardaccounts[$i]['name']			= 'Debitor';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '4';
$standardaccounts[$i]['setting']			= 'debtor';
$standardaccounts[$i]['balance_account']	= 1;

$i++;

$standardaccounts[$i]['number']	= '58000';
$standardaccounts[$i]['name']			= 'Bank, folio';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '4';
$standardaccounts[$i]['balance_account']			= 1;

$i++;

$standardaccounts[$i]['number']	= '58200';
$standardaccounts[$i]['name']			= 'Kassen';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '4';
$standardaccounts[$i]['balance_account']			= 1;

$i++;

$standardaccounts[$i]['number']	= '58990';
$standardaccounts[$i]['name']			= 'Likvide beholdninger';
$standardaccounts[$i]['type_key']			= '3';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '58000';
$standardaccounts[$i]['sum_to']	= '58995';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '58995';
$standardaccounts[$i]['name']			= 'Aktiver i alt';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '50000';
$standardaccounts[$i]['sum_to']	= '58995';
$standardaccounts[$i]['use_key']			= '1';

$i++;


/**********************************************/

$standardaccounts[$i]['number']	= '60000';
$standardaccounts[$i]['name']			= 'Passiver';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '60500';
$standardaccounts[$i]['name']			= 'Kapitalkonto';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['capital_account'] = 1;

$i++;

$standardaccounts[$i]['number']	= '60800';
$standardaccounts[$i]['name']			= 'Årets resultat';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['setting']			= 'result';

$i++;

$standardaccounts[$i]['number']	= '61000';
$standardaccounts[$i]['name']			= 'Privatkonto';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;


$standardaccounts[$i]['number']	= '63100';
$standardaccounts[$i]['name']			= 'Kortfristet gæld';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '65100';
$standardaccounts[$i]['name']			= 'Langfristet gæld';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;


/**********************************************/

$standardaccounts[$i]['number']	= '66000';
$standardaccounts[$i]['name']			= 'Momskonti';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';

$i++;

$standardaccounts[$i]['number']	= '66100';
$standardaccounts[$i]['name']			= 'Moms, indgående, køb';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['setting']			= 'vat_in';

$i++;

$standardaccounts[$i]['number']	= '66150';
$standardaccounts[$i]['name']			= 'Moms af varekøb i udlandet';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['setting']			= 'vat_abroad';

$i++;

$standardaccounts[$i]['number']	= '66200';
$standardaccounts[$i]['name']			= 'Moms, udgående, salg';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['setting']			= 'vat_out';

$i++;

$standardaccounts[$i]['number']	= '66900';
$standardaccounts[$i]['name']			= 'Moms, tilsvar';
$standardaccounts[$i]['type_key']			= '4';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['setting']			= 'vat_balance';

$i++;

$standardaccounts[$i]['number']	= '66990';
$standardaccounts[$i]['name']			= 'Passiver i alt';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '60000';
$standardaccounts[$i]['sum_to']	= '70000';
$standardaccounts[$i]['use_key']			= '1';
$standardaccounts[$i]['balance_account_id_end']			= true;


$i++;

$standardaccounts[$i]['number']	= '98000';
$standardaccounts[$i]['name']			= 'Balancen i alt';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '50000';
$standardaccounts[$i]['sum_to']	= '97999';
$standardaccounts[$i]['use_key']			= '1';


$i++;

$standardaccounts[$i]['number']	= '99000';
$standardaccounts[$i]['name']			= 'Kontrol';
$standardaccounts[$i]['type_key']			= '1';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['use_key']			= '1';


$i++;

$standardaccounts[$i]['number']	= '99990';
$standardaccounts[$i]['name']			= 'Balancekontrol';
$standardaccounts[$i]['type_key']			= '5';
$standardaccounts[$i]['vat_key']	= '0';
$standardaccounts[$i]['sum_from']	= '1';
$standardaccounts[$i]['sum_to']	= '99990';
$standardaccounts[$i]['use_key']			= '1';

$i++;
?>