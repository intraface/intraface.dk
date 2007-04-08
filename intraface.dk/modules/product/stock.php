<?php
require('../../include_first.php');

$kernel->module("product");
$kernel->module("stock");

if (!empty($_POST['submit'])) {

	foreach ($_POST['id'] AS $key=>$values) {
	/*
	NOTE!!!
	Pointen i det hele er man udvælger et array, som man gennemløber - i dette tilfælde
	date - det kunne lige så godt være amount - det eneste der skal bruges er $key for vi
	ved hvilken position den nuværende værdi har i POST arrayed på det enkelte element.
	*/
   $stock = new Stock(new Product($kernel, $_POST['id'][$key]));
   $stock->set($_POST['quantity'][$key]);
  }

}

$stock = new Product($kernel);
$list = $stock->getList("stock", '', @$_GET['c']);

$page = new Page($kernel);
$page->start("Lager");
?>
<h1>Lager</h1>


<?php if (count($list) > 0) { ?>

<?php
echo '<div style="text-align: center; margin: 1em">- ';
foreach ($stock->getCharacters() AS $c) {
	echo '<a href="?c='.$c.'">'.strtolower($c).'</a> - ';
}
echo '</div>';


?>

<?php if (count($list) > 100) { echo '<p>Der vises kun 100 poster ad gangen. Lav nogle søgekriterier.</p>'; } ?>

<form action="stock.php" method="post">
	<table summary="Produkter">
		<thead>
			<tr>
				<th>Navn</th>
				<th>Antal</th>
				<th>Reserveret (faktura)</th>
				<th>Reserveret (webshop)</th>
				<th>I alt på lager</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($list AS $product) { ?>
			<tr>
				<td><?php echo htmlentities($product['name']); ?></td>
				<td>
      		<input type="text" name="quantity[]" value="<?php echo $product['quantity']; ?>" />
      		<input type="hidden" name="id[]" value="<?php echo $product['id']; ?>" />
      	</td>
				<td><?php echo $product['invoice_reserved']; ?></td>
				<td><?php echo $product['webshop_reserved']; ?></td>
				<td><?php echo $product['actual_stock']; ?></td>
			</tr>
			<?php } // end foreach ?>
		</tbody>
	</table>
  <div>
  	<input type="submit" name="submit" value="Opdater lageret" class="save" />
  </div>
</form>
<?php
}
else {
?>
	<p>Der er endnu ikke oprettet nogen lagervarer i databasen.</p>
<?php
}
$page->end();
?>