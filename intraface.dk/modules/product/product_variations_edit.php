<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product = new Product($kernel, $_POST['id']);
    
    if(!empty($_POST['save']) || !empty($_POST['save_and_close'])) {
        
        foreach($_POST['variation'] AS $variation_data) {
            
            if(isset($variation_data['used'])) {
                if(!empty($variation_data['id'])) {
                    // update existing
                    $variation = $product->getVariation($variation_data['id']);
                    
                }
                else {
                    $variation = $product->getVariation();
                    $variation->product_id = $_POST['id'];
                    $variation->setAttributesFromArray($variation_data['attributes']);
                    $variation->save();
                    
                }
                
                $detail = $variation->getDetail();
                $detail->price_difference = 0; /* Can be reimplemented: intval($variation_data['price_difference']); */
                $detail->weight_difference = intval($variation_data['weight_difference']);
                $detail->save();
                
            }
            elseif(!empty($variation_data['id'])) {
                $variation = $product->getVariation($variation_data['id']);
                $variation->delete();
            }   
        }
        
        if(!empty($_POST['save_and_close'])) {
            header('Location: product.php?id='.$product->getId());
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $product = new Product($kernel, $_GET['id']);
    $existing_groups = array();
    
}

$groups = $product->getAttributeGroups();

$page = new Intraface_Page($kernel);
$page->start(t('Edit variations for product').' '.$product->get('name'));
?>
<h1><?php e(t('Edit variations for product').' '.$product->get('name')); ?></h1>

<ul class="options">
    <li><a class="new" href="product_select_attribute_groups.php?id=<?php e($product->getId()); ?>"><?php e(t('Choose attribute groups')); ?></a></li>
    <li><a href="product.php?id=<?php e($product->get('id')); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<?php if (count($groups) == 0): ?>
    <p><?php e(t('No attribute groups has been selected.')); ?> <a href="product_select_attribute_group.php?id=<?php e($product->getId()); ?>"><?php e(t('Choose attribute groups')); ?></a>.</p>
<?php else: ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
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
            if(isset($groups[1]) && is_array($groups[1]) && !empty($groups[1]['id'])) {
                $attributes2 = $group_gateway->findById($groups[1]['id'])->getAttributes();
            } else {
                $attributes2 = array(NULL);
            }
            
            $count = 0;
            ?>
            <?php foreach($attributes1 AS $a1): ?>
                <?php foreach($attributes2 AS $a2): ?>
                    <tr>
                        <td>
                            <?php
                            $attributes['attribute1'] = $a1->getId();
                            if($a2 != NULL) {
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
                            <input type="checkbox" name="variation[<?php echo intval($count); ?>][used]" value="1" <?php if($variation !== NULL) echo 'checked="checked"'; ?> />
                            <input type="hidden" name="variation[<?php echo intval($count); ?>][id]" value="<?php if($variation !== NULL) e($variation->getId()); ?>" />
                            <input type="hidden" name="variation[<?php echo intval($count); ?>][attributes][attribute1]" value="<?php e($a1->getId()); ?>" />
                            <?php if($a2 != NULL): ?> <input type="hidden" name="variation[<?php echo intval($count); ?>][attributes][attribute2]" value="<?php e($a2->getId()); ?>" /><?php endif; ?>
                        </td>
                        <td><?php if($variation !== NULL): e($variation->getNumber()); else: e('-'); endif; ?>
                        </td>
                        <td>
                            <?php 
                            e($groups[0]['name'].': '.$a1->getName());
                            if($a2 != NULL) e(', '.$groups[1]['name'].': '.$a2->getName());
                            ?>
                        </td>
                        <?php /* can be reimplemented: <td><input type="text" name="variation[<?php echo intval($count); ?>][price_difference]" value="<?php if($variation !== NULL) e($variation->getDetail()->getPriceDifference()); ?>" size="4"/></td> */ ?>
                        <td><input type="text" name="variation[<?php echo intval($count); ?>][weight_difference]" value="<?php if($variation !== NULL) e($variation->getDetail()->getWeightDifference()); ?>" size="4" /></td>
                    </tr>
                    <?php
                    $count++;
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" name="save" value="<?php e(t('Save', 'common')); ?>" /> <input type="submit" name="save_and_close" value="<?php e(t('Save and close', 'common')); ?>" />
<?php endif; ?>
</form>


<?php
$page->end();
?>