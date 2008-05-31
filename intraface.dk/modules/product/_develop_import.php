<?php
require('../../include_first.php');

$kernel->useShared('keyword');


$module = $kernel->module("products");
$module->includeFile("Product.php");
$module->includeFile("ProductDetail.php");

$error = array();
/*
if (!empty($_POST['import'])) {

	if (!empty($_POST['delete_old_products'])) {
  	$product = new Product($kernel);
  	foreach($product->getList("all") AS $p) {
    	$po = new Product($kernel, $p['id']);
    	$po->delete();
		}
	}

	$file_id = (int)$_POST['file_id'];

	$csv_file = UPLOAD_PATH.$file_id;

	$handle = fopen($csv_file, "r");
	while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		for ($c=0; $num = sizeof($data); $c < $num; $c++) {
			$row[$c] = $data[$c];
		}

		$var['name'] = $row[$_POST['name']];
		$var['description'] = $row[$_POST['description']];
		$var['price'] = $row[$_POST['price']];
		$var['on_stock'] = $row[$_POST['on_stock']];
		$var['keyword'] = $row[$_POST['keyword']];

		$var['unit'] = 2;
		$var['vat'] = 1;
		$var['active'] = 1;
		$var['do_show'] = 1;
		$var['stock'] = 1;

		$product = new Product($kernel);
		$product->update($var);

		if ($product->error->isError()) {
			$error['error'][] = $product->error;
		}

		$stock = new Stock($product);
		$stock->set($var['on_stock']);

		$keyword = $product->getKeywords();

		if ($add_keyword_id = $keyword->update(array('keyword'=>$var['keyword']))) {
			$keyword->addKeyword($add_keyword_id);
		}
	}
	fclose($handle);

	$manager = new FileManager($kernel, $file_id);
  $manager->delete();


	if (count($error) == 0) {
	  header("Location: index.php");
  	exit;
  }

}
 */

if (!empty($_FILES['userfile']['name'])) {

	$manager = new FileManager($kernel);
	$manager->uploader->setMaxFileSize(300000);
  $manager->uploader->setPermittedFiles(array("text/plain"));
	$file_id = $manager->upload($_FILES['userfile']);

  // HACK :: HACK :: HACK
	$csv_file = UPLOAD_PATH.$file_id;

}

$page = new Intraface_Page($kernel);
$page->start('Importer varer');
?>
<h1>Importer varer</h1>

<?php
foreach ($error AS $e) {
	$e['error']->view();
}

?>

<ul>
<li>Skrive noget der sammenligner produkterne fra databasen med dem der skal indtastes, hvis de er ens, hvad skal der så ske?</li>
<li>Gøre det muligt at styre de uploadede filer, så det er til at holde styr på dem, og så man kan slette dem igen?</li>
</ul>

<?php
if ($csv_file) {
	echo '<h2>Eksempel fra indholdet i den uploadede fil</h2>';
  echo '<table>';
  $row = 1;
  $header = 0;
  $handle = fopen($csv_file, "r");
  while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
     $num = count($data);
  		if ($row == 4) break;
     if ($header < 1) {
     	echo '<tr>';
      $row_name = '';
				for ($c=0; $c < $num; $c++) {
      	echo '<th>#' . $c . '</th>';
       $row_name .= '<option value="'.$c.'">#'.$c.'</option>';
      }
     	echo '</tr>';
     	$header = 1;
     }

     echo "<tr>\n";
     $row++;
     for ($c=0; $c < $num; $c++) {
         echo '<td>' . $data[$c] . "</td>\n";
     }
     echo '</tr>';
  }
  fclose($handle);
  echo '</table>';



?>

<h2>Put oplysningerne i tilhørende felter</h2>

	<?php if (count($msg) > 0) { ?>
	<ul class="formerrors">
	<?php foreach ($msg AS $error) { ?>
	<li><?php echo $error; ?></li>
	<?php } ?>
	</ul>
	<?php } ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset>
	<input type="hidden" name="file_id" value="<?php echo $file_id; ?>">
	<div class="formrow">
  	<label for="name">Navn</label><select name="name"><?php echo $row_name; ?></select>
	</div>
	<div class="formrow">
  	<label for="name">Beskrivelse</label><select name="description"><?php echo $row_name; ?></select>
	</div>
	<div class="formrow">
  	<label for="price">Pris</label><select name="price"><?php echo $row_name; ?></select>
	</div>
	<div class="formrow">
  	<label for="on_stock">På lager</label><select name="on_stock"><?php echo $row_name; ?></select>
	</div>
	<div class="formrow">
  	<label for="keyword">Nøgleord</label><select name="keyword"><?php echo $row_name; ?></select>
	</div>

	<div style="clear:both; margin-top: 1em;">
  	<input type="checkbox" value="yes" name="delete_old_products" id="delete_old_products" /> <label for="delete_old_products">Slet de gamle produkter i databasen</label>
	</div>

  <p><input type="submit" value="Importer" name="import" /></p>
</fieldset>
</form>
<?php
}
else {
?>


<?php echo $msg; ?>
<p>Vær meget forsigtig med denne funktion. Den tjekker ikke, om input er ordentligt. Det skal være en fil separaret af semikolon.</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="editproduct" enctype="multipart/form-data">
<fieldset>
		<p><strong>Note:</strong> Du må kun bruge a-z og 0-9 i filnavnet.</p>
		<p>Du kan uploade <var>.jpg</var>- og <var>.png</var>-filer og filerne må maksimalt fylde 300KB.</p>
	 	<label for="userfile">Filnavn</label>
  	<input name="userfile" type="file" id="userfile" />
    <input type="submit" value="Importer" name="submit" />
   </fieldset>
    </form>
<?php
}
$page->end();
?>
