<?php
require('/home/intraface/devel_intraface/config/configuration.php');
require_once 'I18Nv2/I18Nv2.php';
require_once 'Date.php';
require_once 'MDB2.php';
require_once 'Validate.php';

// let us say, that we are getting a date from the database
echo 'Date from db: ';
echo $db_date = '2006-10-31'; // datetime
echo '<br>Currency from DB';
echo $db_currency = (200050 / 100); // DKK 2.000,50

// we make a date object
$date = new Date($db_date);

// create a locale - we choose Danish
$locale = &I18Nv2::createLocale('da_DK');

// lets output the values for instance to a form
echo '<br>Currency value: ';
echo $amount = $locale->formatCurrency($db_currency, I18Nv2_CURRENCY_INTERNATIONAL), "\n";
echo '<br>Currency value to form: ';

// not completely satisfying to run a regular number_format? Will this be secure enough cross language
echo $amount_to_form = number_format($db_currency, 2, I18Nv2::getInfo('decimal_point'), '');

echo "<br>Format date:              ",
    $form_date = $locale->formatDate($date->getTime(), I18Nv2_DATETIME_SHORT), "\n";
    $form_date_format = $locale->dateFormats[I18Nv2_DATETIME_SHORT];
    echo '('.$form_date_format.')';

// now the user submits the values again, and we need them back into the database

// we now the format the user is supposed to input dates in, and validates according to that
if (!Validate::date($form_date, array('format' => $form_date_format))) {
	echo '<br>Date is not validated';
	exit(0);
}

// validating the amount from the user
if (!Validate::number($amount_to_form, array('decimal', I18Nv2::getInfo('decimal_point')))) {
	echo '<br>Amount not validated';
}

// converting the amount back to the database - afterwards we need to make sure that the conversion was correct
echo '<br>Amount to db ' . str_replace(I18Nv2::getInfo('decimal_point'), '.', $amount_to_form) * 100;

echo '<br>Date to db: ';
echo date('Y-m-d', strtotime($form_date));
// how would we go about and have the date converted
echo '<br>';
highlight_string(file_get_contents(__FILE__));
?>