<?php
require('../../include_first.php');

$module = $kernel->module("product");
$translation = $kernel->getTranslation('product');

if (!empty($_POST)) {
    foreach ($_POST['name'] AS $key=>$value) {
        $product = new Product($kernel, $key);
        if ($product->save(array(
            'number' => $product->get('number'),
            'name' => $_POST['name'][$key],
            'description' => $_POST['description'][$key],
            'unit' => $product->get('unit_key'),
            'vat' => $product->get('vat'),
            'price' => $_POST['price'][$key],
            'weight' => $product->get('weight'),
            'do_show' => $product->get('do_show'),
            'stock' => $product->get('stock'),
            'state_account_id' => $product->get('state_account_id')
        ))) {
            // 'quantity' => $_POST['quantity'][$key], gammelt lager - udgår
            
            $string_appender = new Intraface_Keyword_StringAppender($product->getKeywords(), $product->getKeywordAppender());
            $string_appender->addKeywordsByString($_POST['keywords'][$key]);
        }
        echo $product->error->view();
    }

    header('Location: index.php?use_stored=true');
    exit;
}

if (empty($_GET['use_stored'])) {
    die('Batchredigering kun mulig med gemte resultater');
}


// hente liste med produkter - bør hentes med getList!
$product = new Product($kernel);
$product->getDBQuery()->defineCharacter("character", "detail.name");
$product->getDBQuery()->usePaging("paging");
$product->getDBQuery()->storeResult("use_stored", "products", "toplevel");
$products = $product->getList();

$page = new Intraface_Page($kernel);
$page->start(t('products'));
?>
<h1><?php e(t('products')); ?></h1>

<ul class="options">
    <li><a href="index.php?use_stored=true"><?php e(t('close')); ?></a></li>
</ul>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?php $i = 0; foreach ($products AS $p) {
    if ($p['locked'] == 1) { continue; }
    $this_product = new Product($kernel, $p['id']);
    $keyword_object = $this_product->getKeywordAppender();
    $p['keywords'] = $keyword_object->getConnectedKeywordsAsString();
?>
<table <?php if ($i == 1) { echo ' class="even"'; $i = -1; } ?>>
    <tbody>
        <tr>
            <th><?php e(t('name')); ?></th>
            <td><input size="50" type="text" name="name[<?php echo $p['id']; ?>]" value="<?php echo htmlentities($p['name']); ?>" /></td>
        </tr>
        <tr>
            <th><?php e(t('description')); ?></th>
            <td><textarea cols="80" rows="5" name="description[<?php echo $p['id']; ?>]"><?php echo htmlentities($p['description']); ?></textarea></td>
        </tr>
        <tr>
            <th><?php e(t('price')); ?></th>
            <td><input size="10" type="text" value="<?php echo number_format($p['price'], 2, ",", "."); ?>" name="price[<?php echo $p['id']; ?>]" /> kroner excl. moms</td>
        </tr>
        <tr>
            <th><?php e(t('keywords')); ?></th>
            <td><input size="50"  type="text" value="<?php echo $p['keywords']; ?>" name="keywords[<?php echo $p['id']; ?>]" /></td>
        </tr>
    </tbody>
</table>
<br />
<?php $i++; } // end foreach ?>
<div>
    <input type="submit" class="save" value="<?php e(t('save')); ?>" />
    eller <a href="index.php?use_stored=true"><?php e(t('regret')); ?></a>
</div>
</form>

<?php
$page->end();
?>