<h1><?php e(t('Select product variation')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (count($variations) == 0) : ?>
    <p><?php e(t('No variations created for the product')); ?>.</p>
<?php else : ?>
    <form action="<?php e(url(null)); ?>" method="post">
        <input type="hidden" name="product_id" value="<?php e($product->getId()); ?>" />
        <input type="hidden" name="set_quantity" value="<?php e($context->quantity); ?>" />
        <table summary="<?php e(t('Variations')); ?>" id="variations_table" class="stripe">
            <caption><?php e(t('Variations')); ?></caption>
            <thead>
                <tr>
                    <th><?php if ($context->multiple && $context->quantity) :
                        e(t('Quantity'));
else :
    echo e(t('Choose'));
endif; ?></th>
                    <th>#</th>
                    <th><?php e(t('Variation')); ?></th>
                    <th><?php e(t('Price')); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($variations as $variation) : ?>
                <tr>
                    <td>
                        <?php if ($context->multiple && $context->quantity) : ?>
                            <input id="<?php e($variation->getId()); ?>" type="text" name="selected[<?php e($variation->getId()); ?>]" value="<?php echo '0' ?>" size="2" />
                        <?php elseif ($context->multiple && !$context->quantity) : ?>
                            <input id="<?php e($variation->getId()); ?>" type="checkbox" name="selected[<?php e($variation->getId()); ?>]" value="1" />
                        <?php elseif (!$context->multiple) : ?>
                            <input id="<?php e($variation->getId()); ?>" type="radio" name="selected" value="<?php e($variation->getId()); ?>" />
                        <?php endif; ?>
                    </td>
                    <td><?php e($variation->getNumber()); ?></td>
                    <td><?php e($variation->getName()); ?></td>
                    <td><?php e($variation->getDetail()->getPrice($product)->getAsLocal('da_dk', 2)); ?> <?php e(t('excl. vat')); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p>
        <?php if (!$context->multiple && $context->quantity) : ?>
            <?php e(t('Quantity')); ?>: <input type="text" name="quantity" value="1" />
        <?php endif; ?>
        <?php if ($context->multiple) : ?>
        <input type="submit" name="submit" value="<?php e(t('save')); ?>" />
        <?php endif; ?>
        <input type="submit" name="submit_close" value="<?php e(t('save and close')); ?>" /></p>
    </form>
<?php endif; ?>
