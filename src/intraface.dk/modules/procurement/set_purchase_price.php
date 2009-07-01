<?php
require('../../include_first.php');

$kernel->module("procurement");
$product_module = $kernel->useModule('product');
$translation = $kernel->getTranslation('procurement');

settype($_GET['id'], 'integer');

if (isset($_POST['submit']) && $_POST['submit'] != "") {

	$procurement = new Procurement($kernel, intval($_POST["id"]));

	foreach ($_POST['items'] AS $item) {
		$procurement->loadItem($item['id']);
		$procurement->item->setPurchasePrice($item['price']);
        $procurement->error->merge($procurement->item->error->getMessage());
	}

    if (!$procurement->error->isError()) {
        header("Location: view.php?id=".$procurement->get("id"));
        exit;
    }

} elseif (isset($_GET['id']) && isset($_GET['return_redirect_id'])) {
	$procurement = new Procurement($kernel, $_GET["id"]);
	$redirect = Intraface_Redirect::factory($kernel, 'return');
    $return_parameter = $redirect->getParameter('product_id', 'with_extra_value');
    $redirect->delete();
    if (is_array($return_parameter) && count($return_parameter) > 0) {
		foreach ($return_parameter AS $return) {
		    $return['value'] = unserialize($return['value']);
            $procurement->loadItem();
            $procurement->item->save(array('product_id' => $return['value']['product_id'], 'product_variation_id' => $return['value']['product_variation_id'],  'quantity' => intval($return['extra_value'])));
        }
	} else {
		header('location: view.php?id='.$procurement->get('id'));
		exit;
	}


} else {
	trigger_error("Der mangler id eller return_redirect_id", E_USER_ERROR);
}

$procurement->loadItem();
$items = $procurement->item->getList();

$page = new Intraface_Page($kernel);
$page->start("Angiv indkøbspris");
?>
<h1>Angiv indkøbspris</h1>

<?php echo $procurement->error->view(); ?>

<form method="POST" action="<?php e($_SERVER['PHP_SELF']); ?>" id="form_items">

	<table class="stripe">
        <caption>Produkter</caption>
        <thead>
            <tr>
                <th>Varenr</th>
                <th>Navn</th>
                <th>Antal</th>
                <th>Salgspris</th>
                <th>Indkøbspris pr. stk. ex. moms</th>
  		    </tr>
        </thead>
        <tbody>
  		    <?php for ($i = 0, $max = count($items); $i < $max; $i++): ?>
                <tr>
                    <td align="right">
                        <?php e($items[$i]['number']); ?>
                        <input type="hidden" name="items[<?php e($i); ?>][id]" value="<?php e($items[$i]['id']); ?>" />
                    </td>
                    <td><?php e($items[$i]["name"]) ?></td>
                    <td><?php e($items[$i]['quantity']); ?> <?php e($translation->get($items[$i]['unit'], 'product')) ?></td>
                    <td align="right"><?php e($items[$i]["price"]->getAsLocal('da_dk', 2)); ?></td>
                    <td><input type="input" name="items[<?php e($i); ?>][price]" value="0,00" size="8" /></td>
                </tr>
  			<?php endfor; ?>
        </tbody>
    </table>


<input type="submit" name="submit" value="Gem" class="save" /> eller <a href="view.php?id=<?php e($procurement->get("id")); ?>">Spring over</a>
<input type="hidden" name="id" value="<?php e($procurement->get("id")); ?>" />
</form>
<?php


$page->end();
?>