<?php
/*

@todo Skrive den ind med alle butikkerne. Der kan kun overføres filer, hvis der er noget ftp indtastet.
@todo Tjekke for filer, der ikke længere skal bruges, som så bør slettes på upload-serveren.

*/

require('../../include_first.php');
include('Net/FTP.php');

$module = $kernel->module('product');

$error = new Error;

$setting_is_ok = false;
$transfer_ok = false;

if ($kernel->setting->get('intranet', 'product.ftp.hostname') && $kernel->setting->get('intranet', 'product.ftp.username') && $kernel->setting->get('intranet', 'product.ftp.password') && $kernel->setting->get('intranet', 'product.ftp.directory')) {
	$setting_is_ok = true;
}

if (!empty($_GET['action']) AND $_GET['action'] == 'transfer' AND $setting_is_ok) {
  // hente liste med produkter - bør hentes med getList!
  $product = new Product($kernel, $p['id']);
  $product->createDBQuery();
  $list = $product->getList('webshop');

  $ftp = new Net_FTP();
  $ftp->setHostname($kernel->setting->get('intranet', 'product.ftp.hostname'));
  $ftp->setUsername($kernel->setting->get('intranet', 'product.ftp.username'));
  $ftp->setPassword($kernel->setting->get('intranet', 'product.ftp.password'));

  if (!empty($_POST)) {
    if (!$ftp->connect()) {
    	$error->set('Der kunne ikke etableres kontakt til ftp-serveren.');
    }
    if (!$ftp->login()) {
    	$error->set('Loginoplysningerne til ftp-serveren er forkerte.');
    }
    $db = new DB_Sql;
    if (!$error->isError()) {
      foreach ($list AS $product) {
				//$db->query(); // her bør den hente filnavnet, så det er det rigtige filnavn den uploader
				// dog kræver det, at product også tager filnavnet rigtigt ud
      	$ftp->put('/var/www/onlinefaktura.dk/upload/' . $product['pic_id'], $kernel->setting->get('intranet', 'product.ftp.directory') . $product['pic_id'] . '.jpg', true);
      }
    }
  }
  $transfer_ok = true;

	// for ajax
	if (!empty($_SERVER['HTTP_ACCEPT']) AND $_SERVER['HTTP_ACCEPT'] == 'message/x-jl-formresult') {
		echo 1;
		exit;
	}
}

$page = new Page($kernel);
$page->start('Overfør billeder');
?>
<h1>Overfør billeder</h1>


<?php if ($setting_is_ok AND !$transfer_ok) { ?>
  <?php if (is_object($error)) echo $error->view(); ?>
  <p><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=transfer">Overfør billeder</a></p>
<?php } elseif ($setting_is_ok AND $transfer_ok) {?>
	<p>Billederne er overført</p>
<?php } else { ?>
  <p>Der er ikke lavet nogen indstillinger endnu.</p>
<?php } ?>


<?php
$page->end();
?>