<?php
require('../../include_first.php');

$kernel->module("procurement");
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('procurement');

settype($_GET['id'], 'integer');

if(isset($_POST['submit']) && $_POST['submit'] != "") {

	$procurement = new Procurement($kernel, intval($_POST["id"]));
	if(!is_array($_POST['quantity'])) {
		trigger_error("Fejl i gemningen", E_USER_ERROR);
	}

	foreach($_POST['quantity'] AS $product_id => $quantity) {
		$procurement->loadItem();
		$procurement->item->update(array('product_id' => $product_id, 'quantity' => $quantity, 'dk_unit_purchase_price' => $_POST['price'][$product_id]));
	}

	// Hmm, der kan faktisk opstå problemer her, som vi ikke ser.
	header("Location: view.php?id=".$procurement->get("id"));
  exit;


} elseif(isset($_GET['id']) && isset($_GET['return_redirect_id'])) {
	$procurement = new Procurement($kernel, $_GET["id"]);
	$redirect = Redirect::factory($kernel, 'return');

	$product_id = $redirect->getParameter('product_id');

	if(is_array($product_id) && count($product_id) > 0) {
		$product = new Product($kernel);
		$product->getDBQuery()->setCondition('product.id IN('.implode(',', $product_id).')');

		$products = $product->getList();
	} else {
		header('location: view.php?id='.$procurement->get('id'));
		exit;
	}
} else {
	trigger_error("Der mangler id eller return_redirect_id", E_USER_ERROR);
}


$page = new Intraface_Page($kernel);
$page->start("Sæt antal");
?>
<h1>Sæt antal</h1>

<?php if(!empty($_POST)): echo $procurement->item->error->view(); endif; ?>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="form_items">
<fieldset>
	<legend>Produkter</legend>

  <table>
  	<thead>
  		<tr>
				<th>Antal</th>
				<th>Varenr</th>
  			<th>Navn</th>
				<th>Indkøbspris</th>
  			<th>Salgspris</th>
  		</tr>
  	</thead>
  	<tbody>
  		<?php

  		for($i = 0, $max = count($products); $i < $max; $i++) {
				?>
  			<tr>
  				<td><input type="input" name="quantity[<?php print($products[$i]["id"]); ?>]" value="1" size="3" /> <?php print($translation->get($products[$i]["unit"]['combined'])) ?></td>
					<td align="right"><?php print($products[$i]["number"]); ?></td>
  				<td><?php print(safeToHtml($products[$i]["name"])) ?></td>
					<td><input type="input" name="price[<?php print($products[$i]["id"]); ?>]" value="0,00" size="8" /></td>
  				<td align="right"><?php print(number_format($products[$i]["price"], 2, ",", ".")) ?></td>
  			</tr>
  			<?php
  		}
  		?>
  	</tbody>
  </table>
</fieldset>

<input type="submit" name="submit" value="Gem" class="save" /> eller <a href="view.php?id=<?php echo $procurement->get("id"); ?>">Fortryd</a>
<input type="hidden" name="id" value="<?php print($procurement->get("id")); ?>" />
</form>
<?php


$page->end();
?>