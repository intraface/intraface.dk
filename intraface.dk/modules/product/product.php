<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if(isset($_POST['append_file_submit'])) {

		$product = new Product($kernel, $_POST['id']);

		$filehandler = new FileHandler($kernel);
		$append_file = new AppendFile($kernel, 'product', $product->get('id'));

		if(isset($_FILES['new_append_file'])) {
			$filehandler = new FileHandler($kernel);

			$filehandler->loadUpload();
			if ($product->get('do_show') == 1) { // if shown i webshop
				$filehandler->upload->setSetting('file_accessibility', 'public');
			}
			if($id = $filehandler->upload->upload('new_append_file')) {
				$append_file->save(array('file_handler_id' => $id));
			}
		}
	}

	header('Location: product.php?id='.$product->get('id'));
	exit;


}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

	// delete
	if (!empty($_GET['delete'])) {
		$product = new Product($kernel, $_GET['delete']);
		if ($id = $product->delete()) {
			header('Location: index.php?use_stored=true');
			exit;
		}
	}

	// copy product
	elseif (!empty($_GET['copy']) AND is_numeric($_GET['copy'])) {
		$product = new Product($kernel, $_GET['copy']);
		if ($id = $product->copy()) {
			header('Location: product.php?id='.$id);
			exit;
		}
	}

	// this has to be moved to post
	elseif(isset($_GET['delete_appended_file_id'])) {
		$product = new Product($kernel, $_GET['id']);
		$append_file = new AppendFile($kernel, 'product', $product->get('id'), (int)$_GET['delete_appended_file_id']);
		$append_file->delete();
		header('Location: product.php?id='.$product->get('id'));
		exit;

	}

	// Delete related product
	// has to be moved to post
	elseif (!empty($_GET['del_related']) AND is_numeric($_GET['del_related'])) {
		$product = new Product($kernel, $_GET['id']);
		$product->deleteRelatedProduct($_GET['del_related']);
		header('Location: product.php?id='.$product->get('id'));
		exit;
	}

	elseif (!empty($_GET['id'])) {
		$product = new Product($kernel, $_GET['id']);
		$filehandler = new FileHandler($kernel);
		$append_file = new AppendFile($kernel, 'product', $product->get('id'));
	}

	else {
		trigger_error('Ulovligt', E_USER_ERROR);
	}
}









$page = new Page($kernel);
$page->start('Produkt: ' . $product->get('name'));
?>

<div id="colOne">

<div class="box">
	<h2>#<?php echo safeToHtml($product->get('number'));  ?> <?php echo safeToHtml($product->get('name')); ?></h2>
	<ul class="options">
		<?php if ($product->get('locked') != 1) { ?>
		<li><a href="product_edit.php?id=<?php echo $product->get('id'); ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a></li>

		<li><a class="confirm" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo intval($product->get('id')); ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a></li>
		<?php } ?>
		<li><a href="product.php?copy=<?php echo intval($product->get('id')); ?>"><?php echo $translation->get('copy', 'common'); ?></a></li>
		<li><a href="index.php?from_product_id=<?php echo intval($product->get('id')); ?>&amp;use_stored=true"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
	</ul>
	<div><?php echo autoop($product->get('description')); ?></div>
</div>

<table>
	<tr>
		<td>Pris</td>
		<td><?php echo safeToHtml(number_format($product->get('price'), 2, ",", ".")); ?> ex. moms</td>
	</tr>
	<tr>
		<td>Vægt</td>
		<td><?php echo safeToHtml($product->get('weight')); ?> gram</td>
	</tr>
	<tr>
		<td>Enhed</td>
		<td>
			<?php
				// getting settings
				$unit_choises  = $module->getSetting("unit");
				echo safeToHtml($unit_choises[$product->get('unit_id')]);
			?>
		</td>
	</tr>

	<?php if ($kernel->user->hasModuleAccess("webshop")): ?>

	<tr>
		<td>Vis i webshop</td>
		<td>
			<?php
				$show_choises = array(0=>"Nej", 1=>"Ja");
				echo safeToHtml($show_choises[$product->get('do_show')]);
			?>
		</td>
	</tr>

	<!-- her bør være en tidsangivelse -->

	<?php endif; ?>

	<tr>
		<td>Moms</td>
		<td>
			<?php
				$vat_choises = array(0=>"Nej", 1=>"Ja");
				echo safeToHtml($vat_choises[$product->get('vat')]);
			?>
		</td>
	</tr>
	<?php if ($kernel->intranet->hasModuleAccess('stock')): ?>
	<tr>
		<td>Lagervare</td><td>
			<?php
				$stock_choises = array(0=>"Nej", 1=>"Ja");
				echo safeToHtml($stock_choises[$product->get('stock')]);
			?>
		</td>
	</tr>
	<?php endif; ?>
	<?php
		if ($kernel->user->hasModuleAccess('accounting')):
			$mainAccounting = $kernel->useModule("accounting");
	?>
	<tr>
		<td>Bogføres på</td><td>
		<?php
			$year = new Year($kernel);
			if ($year->get('id') == 0) {
				echo 'Året er ikke sat i regnskab';
			}
			else {
				$account = Account::factory($year, $product->get('state_account_id'));
				if ($account->get('name')) {
					echo safeToHtml($account->get('number') . ' ' . $account->get('name'));
				}
				else {
					echo 'Ikke sat';
				}
			}
		?>
		</td>
	</tr>
	<?php endif; ?>
</table>

<?php
if($kernel->user->hasModuleAccess('invoice')) {
	$debtor_module = $kernel->useModule('debtor');
	$invoice = new Debtor($kernel, 'invoice');
	if($invoice->any('product', $product->get('id'))) {
		?>
		<ul class="options">
			<li><a href="<?php print($debtor_module->getPath().'list.php?type=invoice&amp;status=-1&amp;product_id='.$product->get('id')); ?>">Fakturaer med dette produkt</a></li>
		</ul>
		<?php
	}
}
?>


<div id="related_products" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'related') echo ' fade'; ?>">
	<h2>Relaterede produkter</h2>
	<?php if ($product->get('locked') == 0) { ?>
		<ul class="button"><li><a href="related_product.php?id=<?php echo $product->get('id'); ?>">Tilknyt produkter</a></li></ul>
	<?php } ?>
	<?php
		$related = $product->getRelatedProducts();
		if (!empty($related) AND count($related) > 0) {
			foreach ($related AS $p) {
				echo '<li>'. $p['name'];
				if ($p['locked'] == 0) {
					echo ' <a class="delete" href="product.php?id='.$product->get('id').'&amp;del_related='.$p['related_id'].'&amp;from=related#related">Slet</a>';
				}
				echo '</li>';
			}
			echo '</ul>';
		}
	?>
</div>

</div>

<div id="colTwo">

	<div class="box">
		<?php
		//$appendix_list = $append_file->getList();
		if(count($product->get('pictures')) > 0) {
			foreach($product->get('pictures') AS $appendix) {
				echo '<div class="appendix"><img src="'.$appendix['thumbnail']['file_uri'].'" />'.$appendix['original']['name'].' <a class="delete" href="product.php?id='.$product->get('id').'&amp;delete_appended_file_id='.$appendix['appended_file_id'].'">Slet</a></div>';
			}
		}
		?>


		<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST"  enctype="multipart/form-data">
		<input type="hidden" name="id" value="<?php echo intval($product->get('id')); ?>" />
		<input type="hidden" name="detail_id" value="<?php echo intval($product->get('detail_id')); ?>" />

		<?php
		$filehandler = new Filehandler($kernel);
		$filehandler_html = new FileHandlerHTML($filehandler);
		$filehandler_html->printFormUploadTag('', 'new_append_file', 'append_file_choose_file', array('type'=>'only_upload', 'include_submit_button_name' => 'append_file_submit', 'filemanager' => false));
		?>
		</form>
	</div>


	<div id="keywords" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
  	<h2>Nøgleord</h2>
    <?php if ($product->get('locked') == 0) { ?>
    <ul class="button"><li><a href="/shared/keyword/connect.php?product_id=<?php echo $product->get('id'); ?>">Tilknyt nøgleord</a></li></ul>
    <?php } ?>
	<?php
		$keyword = $product->getKeywords();
    	$keywords = $keyword->getConnectedKeywords();
	    if (is_array($keywords) AND count($keywords) > 0) {
			echo '<ul>';
			foreach ($keywords AS $k) {
				echo '<li>' . safeToHtml($k['keyword']) . '</li>';
			}
			echo '</ul>';
		}
    ?>
  </div>


	<?php
	/* HACK HACK HACK MED AT TJEKKE OM oProduct har objektet */
	if($kernel->user->hasModuleAccess("stock") AND is_object($product->stock)) {

		if(isset($_GET['adaptation']) && $_GET['adaptation'] == 'true') {
			$product->stock->adaptation();
		}
		?>
		<div id="stock" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'stock') echo ' fade'; ?>">
			<h2>Lager</h2>

			<table>
				<tr>
					<td>Lagerstatus</td>
					<td><?php print($product->stock->get("actual_stock")); ?></td>
				</tr>
				<tr>
					<td>Bestilt hjem</td>
					<td><?php print($product->stock->get("on_order")); ?></td>
				</tr>
				<tr>
					<td>Reserveret</td>
					<td><?php print($product->stock->get("reserved")); ?> (<?php print($product->stock->get("on_quotation")); ?>)</td>
				</tr>
			</table>
			<!-- hvad bliver følgende brugt til -->
			<div id="stock_regulation" style="display: none ; position: absolute; border: 1px solid #666666; background-color: #CCCCCC; padding: 10px; width: 260px;">
				Reguler med antal: <input type="text" name="regulate_number" size="5" />
				<br />Beskrivelse: <input type="text" name="regulation_description" />
				<br /><input type="submit" name="regulate" value="Gem" /> <a href="javascript:;" onclick="document.getElementById('stock_regulation').style.display='none';return false">[Skjul]</a>

			</div>

			<p><a href="stock_regulation.php?product_id=<?php print($product->get('id')); ?>">Regulering</a> <a href="product.php?id=<?php print($product->get('id')); ?>&amp;adaptation=true" class="confirm">Afstem</a></p>

			<p>Sidst afstemt: <?php echo safeToHtml($product->stock->get('dk_adaptation_date_time')); ?></p>

			<?php
			if($kernel->user->hasModuleAccess('procurement')) {
				$kernel->useModule('procurement');

				$procurement = new Procurement($kernel);
				$latest = $procurement->getLatest($product->get('id'), $product->stock->get("actual_stock"));

				if(count($latest) > 0) {
					?>
					<h3>Seneste indkøb</h3>

					<table>
						<thead>
							<tr>
								<th>Dato</th>
								<th class="amount">Kostpris</th>
								<th class="amount">Antal</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$is_under_actual = true;
						for($i = 0, $max = count($latest); $i < $max; $i++) {
							?>
							<tr>
								<td><?php echo safeToHtml($latest[$i]['dk_invoice_date']); ?></td>
								<td class="amount"><?php print(number_format($latest[$i]['calculated_unit_price'], 2, ",", ".")); ?></td>
								<td class="amount"><?php safeToHtml(print($latest[$i]['quantity'])); ?></td>
								<td>
									<?php
									if(isset($latest[$i]['sum_quantity']) && $latest[$i]['sum_quantity'] >= $product->stock->get("actual_stock") && $is_under_actual) {
										print("<");
										$is_under_actual = false;
									}
									?>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
					<?php
				}
			}
			?>

		</div>
		<?php
	}
	?>

</div>
<?php
$page->end();
?>
