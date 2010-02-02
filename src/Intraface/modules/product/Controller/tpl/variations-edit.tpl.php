<h1><?php e(t('Edit variations for product').' '.$product->get('name')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url('select_attribute_groups')); ?>"><?php e(t('Choose attribute groups')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>


<?php if (count($groups) == 0): ?>
    <p><?php e(t('No attribute groups has been selected.')); ?> <a href="<?php e(url('select_attribute_group')); ?>"><?php e(t('Choose attribute groups')); ?></a>.</p>
<?php else: ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="post">
<input type="hidden" name="id" value="<?php e($product->getId()); ?>" />
    <table summary="<?php e(t('Variations')); ?>" id="variations_table" class="stripe">
        <caption><?php e(t('Variations')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('Use')); ?></th>
                <th><?php e(t('Number')); ?></th>
                <th><?php e(t('Variation')); ?></th>
                <?php /* Ca be reimplemented: <th><?php e(t('Price difference')); ?></th> */ ?>
                <th><?php e(t('Weight difference')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();

            $attributes1 = $group_gateway->findById($groups[0]['id'])->getAttributes();
            if (isset($groups[1]) && is_array($groups[1]) && !empty($groups[1]['id'])) {
                $attributes2 = $group_gateway->findById($groups[1]['id'])->getAttributes();
            } else {
                $attributes2 = array(NULL);
            }

            $count = 0;
            ?>
            <?php foreach ($attributes1 AS $a1): ?>
                <?php foreach ($attributes2 AS $a2): ?>
                    <tr>
                        <td>
                            <?php
                            $attributes['attribute1'] = $a1->getId();
                            if ($a2 != NULL) {
                                $attributes['attribute2'] = $a2->getId();
                            }
                            try {
                                $variation = $product->getVariationFromAttributes($attributes);

                            } catch (Intraface_Gateway_Exception $e) {
                                $variation = NULL;
                            } catch (Exception $e) {
                                $variation = NULL;
                            }
                            ?>
                            <input type="checkbox" name="variation[<?php e($count); ?>][used]" value="1" <?php if ($variation !== NULL) echo 'checked="checked"'; ?> />
                            <input type="hidden" name="variation[<?php e($count); ?>][id]" value="<?php if ($variation !== NULL) e($variation->getId()); ?>" />
                            <input type="hidden" name="variation[<?php e($count); ?>][attributes][attribute1]" value="<?php e($a1->getId()); ?>" />
                            <?php if ($a2 != NULL): ?> <input type="hidden" name="variation[<?php e($count); ?>][attributes][attribute2]" value="<?php e($a2->getId()); ?>" /><?php endif; ?>
                        </td>
                        <td><?php if ($variation !== NULL): e($variation->getNumber()); else: e('-'); endif; ?>
                        </td>
                        <td>
                            <?php
                            e($groups[0]['name'].': '.$a1->getName());
                            if ($a2 != NULL) e(', '.$groups[1]['name'].': '.$a2->getName());
                            ?>
                        </td>
                        <?php /* can be reimplemented: <td><input type="text" name="variation[<?php e($count); ?>][price_difference]" value="<?php if ($variation !== NULL) e($variation->getDetail()->getPriceDifference()); ?>" size="4"/></td> */ ?>
                        <td><input type="text" name="variation[<?php e($count); ?>][weight_difference]" value="<?php if ($variation !== NULL) e($variation->getDetail()->getWeightDifference()); ?>" size="4" /></td>
                    </tr>
                    <?php
                    $count++;
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" name="save" value="<?php e(t('Save')); ?>" />
    <input type="submit" name="save_and_close" value="<?php e(t('Save and close')); ?>" />
<?php endif; ?>
</form>
