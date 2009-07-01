<?php
require('../../include_first.php');

if (empty($_REQUEST['id']) OR !is_numeric($_REQUEST['id'])) {
    trigger_error('Der skal være sat et id på add_related_product.php', E_USER_ERROR);
}

$module = $kernel->module("product");

if (!empty($_POST['product'])) {
    foreach ($_POST['product'] as $key=>$value) {
        $product = new Product($kernel, $_POST['id']);
        if (!empty($_POST['relate'][$key]) AND $product->setRelatedProduct($_POST['product'][$key], $_POST['relate'][$key])) {
        }
    }
    if (!empty($_POST['close'])) {
        header ('Location: product.php?id='.intval($_POST['id']));
        exit;
    }
    header('Location: related_product.php?id='.(int)$_POST['id']);
    exit;
}


$product = new Product($kernel, (int)$_GET['id']);

$related_product_ids = array();
foreach ($product->getRelatedProducts() AS $related) {
    $related_product_ids[] = $related['id'];
}

$keywords = $product->getKeywordAppender();

if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {

    if (isset($_GET["search"])) {
        $product->getDBQuery()->setFilter("search", $_GET["search"]);
    }

    if (isset($_GET["keyword_id"])) {
        $product->getDBQuery()->setKeyword($_GET["keyword_id"]);
    }
} else {
    $product->getDBQuery()->useCharacter();
}

$product->getDBQuery()->defineCharacter("character", "detail.name");
$product->getDBQuery()->setCondition("product.id != " . $_GET['id']);
$product->getDBQuery()->setExtraUri("&amp;id=".(int)$_GET['id']);
$product->getDBQuery()->usePaging("paging");
$product->getDBQuery()->storeResult("use_stored", "related_products", "sublevel");

$list = $product->getList();

$page = new Intraface_Page($kernel);
//$page->includeJavascript('module', 'add_related.js');
$page->start(t('add related products'));
?>
<h1><?php e(t('add related products')); ?></h1>
<p>... <?php e(t('to')); ?> <?php e($product->get('name')); ?></p>

<ul class="options">
    <li><a href="product.php?id=<?php e($_GET['id']); ?>&amp;from=related&amp;use_stored=true#related"><?php e(t('close')); ?></a></li>
</ul>

<form action="<?php echo basename(__FILE__); ?>" method="get">
    <fieldset>
        <legend><?php e(t('search')); ?></legend>
        <!--
        <label>Filter
        <select name="filter" id="filter">
            <option>Ingen</option>
            <option value="notpublished" <?php if (!empty($_GET['filter']) AND $_GET['filter'] == 'notpublished') echo ' selected="selected"'; ?>>Ikke udgivet</option>
            <option value="webshop"<?php if (!empty($_GET['filter']) AND $_GET['filter'] == 'webshop') echo ' selected="selected"'; ?>>Webshop</option>
            <option value="stock"<?php if (!empty($_GET['filter']) AND $_GET['filter'] == 'stock') echo ' selected="selected"'; ?>>Lager</option>
        </select>
        </label>
        -->
        <label><?php e(t('search for')); ?>
        <input type="text" value="<?php e($product->getDBQuery()->getFilter("search")); ?>" name="search" id="search" />
    </label>
    <label>
        Vis med nøgleord
        <select name="keyword_id" id="keyword_id">
            <option value=""><?php e(t('none')); ?></option>
            <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
            <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $product->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
            <?php } ?>
        </select>
    </label>
    <span>
        <input type="submit" value="<?php e(t('go!')); ?>" class="search" />
        <input type="hidden" value="<?php e($product->get('id')); ?>" name="id" />
    </span>
    </fieldset>
    <br style="clear: both;" />
</form>

<?php
echo $product->getDBQuery()->display('character');
?>
<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($product->get('id')); ?>" id="product_id" />
    <table summary="Produkter" class="stripe">
        <caption><?php e(t('products')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('choose')); ?></th>
                <th><?php e(t('product number')); ?></th>
                <th><?php e(t('name')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $p) { ?>
            <tr>
                <td>
                    <input type="hidden" name="product[<?php e($p['id']); ?>]" value="<?php e($p['id']); ?>" />
                    <input class="input-relate" id="<?php e($p['id']); ?>" type="checkbox" name="relate[<?php e($p['id']); ?>]" value="relate" <?php if (array_search($p['id'], $related_product_ids) !== false) echo ' checked="checked"'; ?> />
                </td>
                <td><?php e($p['number']); ?></td>
                <td><?php e($p['name']); ?></td>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>
      <p>
          <input type="submit" value="<?php e(t('save')); ?>" />
          <input type="submit" value="<?php e(t('save and close')); ?>" name="close" />
      </p>

  <?php echo $product->getDBQuery()->display('paging'); ?>
    </form>
<?php
$page->end();
?>