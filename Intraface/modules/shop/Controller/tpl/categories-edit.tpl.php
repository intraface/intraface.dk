<?php
$level_categories = array(0 => $category_object->getAllCategories());

/**
 * @var integer level
 */
$level = 0;
?>

<form action="<?php e(url(null)); ?>" method="post">
<fieldset>
    <legend><?php e(t('Category information')); ?></legend>
        <input type="hidden" name="id" value="<?php e($category_object->getId()); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php e($category_object->getName()); ?>" />
        </div>
        <div class="formrow">
            <label for="identifier"><?php e(t('Identifier')); ?></label>
            <input type="text" name="identifier" id="identifier" value="<?php e($category_object->getIdentifier()); ?>" />
        </div>
        <div class="formrow">
            <label for="parent_id"><?php e(t('Parent category')); ?></label>
            <select name="parent_id" id="parent_id">
            <option value="0"><?php e(t('Choose')); ?></option>
    <?php while($level >= 0): ?>
        <?php foreach($level_categories[$level] as $category): ?>
            <?php array_shift($level_categories[$level]); ?>
            <?php if ($category['id'] == $category_object->getId()) continue; ?>
                <option <?php if ($category_object->getParentId() == $category['id']) echo 'selected="selected"'; ?> value="<?php e($category['id']); ?>">
                    <?php e(str_repeat('- ', $level)); ?> <?php e($category['name']); ?>
                </option>
            <?php
            # If there is subcategories to the category
            if(is_array($category['categories']) && count($category['categories']) > 0) {
                
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
        if(count($level_categories[$level]) == 0) {
            # And we move a level up.
            $level--;
        }
        ?>
    <?php endwhile; ?>



            </select>
        </div>
                
    </fieldset>
    
    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
        <a href="<?php e($regret_link); ?>"><?php e(t('regret', 'common')); ?></a>
    </div>

</form>