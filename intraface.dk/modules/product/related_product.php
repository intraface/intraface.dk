<?php
require('../../include_first.php');

if (empty($_REQUEST['id']) OR !is_numeric($_REQUEST['id'])) {
    trigger_error('Der skal være sat et id på add_related_product.php', E_USER_ERROR);
}

$module = $kernel->module("product");

// hente liste med produkter - bør hentes med getList!

if (!empty($_POST['product'])) {
        foreach ($_POST['product'] AS $key=>$value) {


        $product = new Product($kernel, $_POST['id']);
        if (!empty($_POST['relate'][$key]) AND $product->setRelatedProduct($_POST['product'][$key], $_POST['relate'][$key])) {
        }
    }
    /*
    if (isAjax()) {
        echo '1';
        exit;
    }

    else {
    */
    if (!empty($_POST['close'])) {
        header ('Location: product.php?id='.intval($_POST['id']));
        exit;
    }


        header('Location: related_product.php?id='.(int)$_POST['id']);
        exit;
    //}

}


$product = new Product($kernel, (int)$_GET['id']);
$product->createDBQuery();

$related_products = $product->getRelatedProducts();

$keywords = $product->getKeywordAppender();

if(isset($_GET["search"]) || isset($_GET["keyword_id"])) {

    if(isset($_GET["search"])) {
        $product->dbquery->setFilter("search", $_GET["search"]);
    }

    if(isset($_GET["keyword_id"])) {
        $product->dbquery->setKeyword($_GET["keyword_id"]);
    }
}
else {
    $product->dbquery->useCharacter();
}

$product->dbquery->defineCharacter("character", "detail.name");
$product->dbquery->setCondition("product.id != " . $_GET['id']);
$product->dbquery->setExtraUri("&amp;id=".(int)$_GET['id']);
$product->dbquery->usePaging("paging");
$product->dbquery->storeResult("use_stored", "related_products", "sublevel");

$list = $product->getList();

$page = new Page($kernel);
//$page->includeJavascript('module', 'add_related.js');
$page->start("Tilføj relaterede varer");
?>
<h1>Tilføj relaterede varer</h1>
<p>... til <?php echo $product->get('name'); ?></p>

<ul class="options">
    <li><a href="product.php?id=<?php echo $_GET['id']; ?>&amp;from=related&amp;use_stored=true#related">Luk</a></li>
</ul>

<form action="<?php echo basename(__FILE__); ?>" method="get">
    <fieldset>
        <legend>Søgning</legend>
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
        <label>Søg efter
        <input type="text" value="<?php print($product->dbquery->getFilter("search")); ?>" name="search" id="search" />
    </label>
    <label>
        Vis med nøgleord
        <select name="keyword_id" id="keyword_id">
            <option value="">Ingen</option>
            <?php foreach ($keywords->getUsedKeywords() AS $k) { ?>
            <option value="<?php echo $k['id']; ?>" <?php if($k['id'] == $product->dbquery->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php echo $k['keyword']; ?></option>
            <?php } ?>
        </select>
    </label>
    <span>
        <input type="submit" value="Afsted!" class="search" />
    <input type="hidden" value="<?php echo $product->get('id'); ?>" name="id" />
        </span>
    </fieldset>
    <br style="clear: both;" />
</form>

<?php
echo $product->dbquery->display('character');
?>
<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" name="id" value="<?php echo $product->get('id'); ?>" id="product_id" />
    <table summary="Produkter" class="stripe">
        <caption>Produkter</caption>
        <thead>
            <tr>
                <th>Vælg</th>
                <th>Varenummer</th>
                <th>Navn</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list AS $p) { ?>
            <tr>
                <td>
                    <input type="hidden" name="product[<?php echo $p['id']; ?>]" value="<?php echo $p['id']; ?>" />
                    <input class="input-relate" id="<?php echo $p['id']; ?>" type="checkbox" name="relate[<?php echo $p['id']; ?>]" value="relate" <?php if (array_key_exists($p['id'], $related_products)) echo ' checked="checked"'; ?> />
                </td>
                <td><?php echo htmlentities($p['number']); ?></td>
                <td><?php echo htmlentities($p['name']); ?></td>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>
      <p>
          <input type="submit" value="Gem" />
          <input type="submit" value="Gem og luk" name="close" />
      </p>

  <?php echo $product->dbquery->display('paging'); ?>
    </form>
<?php
$page->end();
?>