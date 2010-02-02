<?php
/**
 * @var array $level_categories Containes the menu items for the level given by the key
 */
$level_categories = array(0 => $categories);

/**
 * @var integer level
 */
$level = 0;
?>

<form action="<?php e(url()); ?>" method="post">
<table>
    <caption><?php e(t('Categories')); ?></caption>
    <thead>
    <tr>
        <th></th>
        <th><?php e(t('Category')); ?></th>
        <th></th>
    </tr>
    </thead>
    <?php while ($level >= 0): ?>
        <?php foreach ($level_categories[$level] AS $category): ?>
            <?php array_shift($level_categories[$level]); ?>
            <tr>
                <td><input id="category_<?php e($category['id']); ?>" type="checkbox" name="category[]" value="<?php e($category['id']); ?>" /></td>
                <td><?php e(str_repeat('- ', $level)); ?><a href="<?php e(url($category['id'])); ?>"><?php e($category['name']); ?></a></td>
                <td><a href="<?php e(url($category['id'] . '/edit')); ?>"><?php e(t('Edit')); ?></a></td>
            </tr>
            <?php
            # If there is subcategories to the category
            if (is_array($category['categories']) && count($category['categories']) > 0) {

                # We make the items for the next level the sub categories of this category
                $level_categories[$level+1] = $category['categories'];

                # We move to next level
                $level++;

                # We break this foreach as we are ready to go next level_categories for new level.
                # Notice that the last code in the while loop will be executed anyway
                break;
            }
            ?>
        <?php endforeach; ?>
        <?php
        # If all elements for the level_categories for this level is gone, we move a level up.
        if (count($level_categories[$level]) == 0) {
            # And we move a level up.
            $level--;
        }
        ?>
    <?php endwhile; ?>
</table>
<?php if (isset($product_id)): ?>
    <input type="hidden" name="product_id" value="<?php e($product_id); ?>" />
    <input type="submit" name="append_product" value="<?php e(t('Select')); ?>" />
<?php else: ?>
    <select name="action">
        <option value=""><?php e(t('Choose...')); ?></option>
        <option value="delete"><?php e(t('Delete selected')); ?></option>
    </select>

    <input type="submit" value="<?php e(t('Go')); ?>" />
<?php endif; ?>
</form>