<h1><?php e(t('Products')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<form action="<?php e(url()); ?>" method="post">
<?php $i = 0; foreach ($products AS $p) {
    if ($p['locked'] == 1) { continue; }
    $this_product = new Product($context->getKernel(), $p['id']);
    $keyword_object = $this_product->getKeywordAppender();
    $p['keywords'] = $keyword_object->getConnectedKeywordsAsString();
?>
<table <?php if ($i == 1) { echo ' class="even"'; $i = -1; } ?>>
    <tbody>
        <tr>
            <th><?php e(t('name')); ?></th>
            <td><input size="50" type="text" name="name[<?php e($p['id']); ?>]" value="<?php e($p['name']); ?>" /></td>
        </tr>
        <tr>
            <th><?php e(t('description')); ?></th>
            <td><textarea cols="80" rows="5" name="description[<?php e($p['id']); ?>]"><?php e($p['description']); ?></textarea></td>
        </tr>
        <tr>
            <th><?php e(t('price')); ?></th>
            <td><input size="10" type="text" value="<?php e(number_format($p['price'], 2, ",", ".")); ?>" name="price[<?php e($p['id']); ?>]" /> kroner excl. moms</td>
        </tr>
        <tr>
            <th><?php e(t('keywords')); ?></th>
            <td><input size="50"  type="text" value="<?php e($p['keywords']); ?>" name="keywords[<?php e($p['id']); ?>]" /></td>
        </tr>
    </tbody>
</table>
<br />
<?php $i++; } // end foreach ?>
<div>
    <input type="submit" class="save" value="<?php e(t('Save', 'common')); ?>" />
    <a href="index.php?use_stored=true"><?php e(t('Cancel', 'common')); ?></a>
</div>
</form>
