<h1><?php e(t('Regulate stock for variation on product')); ?> "<?php e($product->get('name')); ?>"</h1>

<form action="<?php e(url(null)); ?>" method="post">
            <table summary="<?php e(t('Variations')); ?>" id="variations_table" class="stripe">
                <caption><?php e(t('Variations')); ?></caption>
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php e(t('Variation')); ?></th>
                        <th><?php e(t('In stock')); ?></th>
                        <th><?php e(t('Regulate by...')); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($variations AS $variation): ?>
                    <tr>
                        <td><?php e($variation->getNumber()); ?></td>
                        <td><?php e($variation->getName()); ?></td>
                        <td><?php e($variation->getStock($product)->get('actual_stock')); ?></td>
                        <td><input type="text" value="" name="variations[<?php e($variation->getId()); ?>]" size="4" /> <?php e(t('pieces')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
	<input name="submit" type="submit"  value="<?php e(t('Regulate')); ?>" />
	<a href="<?php e(url('../../')); ?>"><?php e(t('Cancel')); ?></a>
</form>