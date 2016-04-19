<h1><?php e(t('Variations for product').' '.$product->get('name')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
    <li><a class="new" href="<?php e(url('select_attribute_groups')); ?>"><?php e(t('Choose attribute groups')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>


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
                $attributes2 = array(null);
            }

            $count = 0;
            ?>
            <?php foreach ($attributes1 as $a1) : ?>
                <?php foreach ($attributes2 as $a2) : ?>
                    <tr>
                        <td>
                            <?php
                            $attributes['attribute1'] = $a1->getId();
                            if ($a2 != null) {
                                $attributes['attribute2'] = $a2->getId();
                            }
                            try {
                                $variation = $product->getVariationFromAttributes($attributes);
                            } catch (Intraface_Gateway_Exception $e) {
                                $variation = null;
                            } catch (Exception $e) {
                                $variation = null;
                            }
                            ?>
                        <?php if ($variation !== null) {
                            echo '&bull;';
} ?>
                        </td>
                        <td><?php if ($variation !== null) :
                            e($variation->getNumber());
else :
    e('-');
endif; ?>
                        </td>
                        <td>
                            <?php
                            e($groups[0]['name'].': '.$a1->getName());
                            if ($a2 != null) {
                                e(', '.$groups[1]['name'].': '.$a2->getName());
                            }
                            ?>
                        </td>
                        <?php /* can be reimplemented: <td><input type="text" name="variation[<?php e($count); ?>][price_difference]" value="<?php if ($variation !== NULL) e($variation->getDetail()->getPriceDifference()); ?>" size="4"/></td> */ ?>
                        <td><?php if ($variation !== null) {
                            e($variation->getDetail()->getWeightDifference());
} ?></td>
                    </tr>
                    <?php
                    $count++;
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>