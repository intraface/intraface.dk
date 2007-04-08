<?php
/**
 * Hvad gør vi egentlig med disse settings. Bør settings, der hører de enkelte moduler
 * til være i en setting-fil under modulet, eller hvad var vi blevet enige om? /LO
 *
 */
 
// userprefences
$_setting['rows_pr_page'] = 20;
$_setting['theme'] = 2;
$_setting['ptextsize'] = 'small';

#
# Muligheder
# 0 => 3x7
# 1 => 2x8
#

$_setting['htmleditor'] = 'tinymce';

$_setting['label'] = 0;

$_setting['language'] = 'dk';

// skulle bruges til at man altid loggende ind i systemet, hvor man var - 
// se kernel - men kan ikke få det til at virke
$_setting['kernel.last_page'] = 'http://www.intraface.dk/main/index.php';

$_setting['homepage.message'] = 'view';
$_setting['homepage.last_view'] = '0000-00-00 00:00:00';

$_setting['vatpercent'] = 25;

$_setting['bank_name'] = '';
$_setting['bank_reg_number'] = '';
$_setting['bank_account_number'] = '';
$_setting['giro_account_number'] = '';

$_setting['webshop.show_online'] = 0;
$_setting['webshop.confirmation_text'] = 'Tak for din bestilling i vores webshop. Vi behandler ordrene så hurtigt vi kan.';
$_setting['webshop.discount_percent'] = '15';
$_setting['webshop.discount_limit'] = '1000';


$_setting['comment.gravatar'] = 'show';
$_setting['comment.gravatar.default_url'] = 'http://www.intraface.dk/images/gravatar/gravatar-default.gif';
$_setting['comment.gravatar.default_size'] = 40;

$_setting['flickr.api_key'] = 'a658de92e636bfeb228d47c9facfd2a9';

$_setting['cc_license'] = 1; // den skrappeste licens


?>