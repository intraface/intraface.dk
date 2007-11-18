<?php
require('../../include_first.php');
require_once 'Intraface/shared/keyword/Keyword.php';
require_once 'Intraface/modules/product/Product.php';

$webshop_module = $kernel->module('webshop');
$translation = $kernel->getTranslation('webshop');

$webshop_module->includeFile('FeaturedProducts.php');

if (!empty($_POST)) {
    $featured = new Intraface_Webshop_FeaturedProducts($kernel->intranet, $db);
    if ($featured->add($_POST['headline'], new Keyword(new Product($kernel), $_POST['keyword_id']))) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$featured = new Intraface_Webshop_FeaturedProducts($kernel->intranet, $db);
$all = $featured->getAll();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('featured products')));

?>
<h1><?php echo safeToHtml($translation->get('featured products')); ?></h1>

<table>
    <caption><?php echo safeToHtml($translation->get('featured products')); ?></caption>
    <thead>
    <tr>
        <th>Overskrift</th>
        <th>Nøgleord</th>
    </tr>
    </thead>
<?php foreach ($all as $feature): ?>
    <tr>
        <td><?php e($feature['headline']); ?></td>
        <td>
        <?php
            $keyword = new Keyword(new Product($kernel), $feature['keyword_id']);
            e($keyword->getKeyword());
        ?>
        </td>
    </tr>
<?php endforeach; ?>
</table>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST">
    <label for="headline">Headline</label> <input id="headline" type="text" name="headline" />
    <label for="keyword_id">Keyword</label>
        <?php
        $keyword_object = new Intraface_Keyword_Appender(new Product($kernel));
        $keywords = $keyword_object->getAllKeywords();
        ?>

    <select id="keyword_id" name="keyword_id">
        <option value="">Vælg...</option>
        <?php foreach ($keywords as $keyword): ?>
        <option value="<?php e($keyword['id']); ?>"><?php e($keyword['keyword']); ?></option>
        <?php endforeach; ?>

    </select>
    <input type="submit" class="save" name="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" /> <?php echo safeToHtml($translation->get('or', 'common')); ?> <a href="index.php"><?php echo safeToHtml($translation->get('cancel', 'common')); ?></a>
</form>

<?php
$page->end();
?>
