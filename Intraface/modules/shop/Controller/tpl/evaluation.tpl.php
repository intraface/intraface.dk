<?php //echo $basketevaluation->error->view($translation); ?>

<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <legend><?php e(t('Information')); ?></legend>
        <input type="hidden" name="id" value="<?php if (isset($value['id'])) e($value['id']); ?>" />

        <div class="formrow">
            <label for="running_index"><?php e(t('index')); ?></label>
            <input type="text" name="running_index" size="6" value="<?php if (isset($value['running_index'])) e($value['running_index']); ?>" />
            <?php e(t('Number that decides the order for the evaluation')); ?>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('evaluation')); ?></legend>

        <div class="formrow">
            <label for="evaluate_target_key"><?php e(t('evaluation target')); ?></label>
            <select name="evaluate_target_key">
                <?php foreach ($settings['evaluate_target'] AS $key => $evaluate_target): ?>
                    <option value="<?php e(intval($key)); ?>" <?php if (isset($value['evaluate_target_key']) && $value['evaluate_target_key'] == $key) echo 'selected="selected"'; ?> ><?php e(t($evaluate_target)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="formrow">
            <label for="evaluate_method_key"><?php e(t('evaluation method')); ?></label>
            <select name="evaluate_method_key">
                <?php foreach ($settings['evaluate_method'] AS $key => $evaluate_method): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($value['evaluate_method_key']) && $value['evaluate_method_key'] == $key) echo 'selected="selected"'; ?> ><?php e(t($evaluate_method)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="formrow">
            <label for="evaluate_value"><?php e(t('evaluation value')); ?></label>
            <input type="text" name="evaluate_value" size="10" value="<?php if (isset($value['evaluate_value'])) e($value['evaluate_value']); ?>" />
        </div>

        <div class="formrow">
            <label for="evaluate_value_case_sensitive"><?php e(t('case sensitive')); ?></label>
            <input type="checkbox" name="evaluate_value_case_sensitive" value="1" <?php if (isset($value['evaluate_value_case_sensitive']) && (int)$value['evaluate_value_case_sensitive'] == 1) echo 'checked="checked"'; ?> />
        </div>

        <div class="formrow">
            <label for="go_to_index_after"><?php e(t('go to index after')); ?></label>
            <input type="text" name="go_to_index_after" size="6" value="<?php if (isset($value['go_to_index_after'])) e($value['go_to_index_after']); ?>" />
        </div>

    </fieldset>

    <fieldset>
        <legend><?php e(t('action')); ?></legend>

        <div class="formrow">
            <label for="action_action_key"><?php e(t('action')); ?></label>
            <select name="action_action_key">
                <?php foreach ($settings['action_action'] AS $key => $action_action): ?>
                    <option value="<?php e(intval($key)); ?>" <?php if (isset($value['action_action_key']) && $value['action_action_key'] == $key) echo 'selected="selected"'; ?> ><?php e(t($action_action)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="formrow">
            <label for="action_value"><?php e(t('target')); ?></label>
            <input type="text" name="action_value" size="30" value="<?php if (isset($value['action_value'])) e($value['action_value']); ?>" />
        </div>

        <div class="formrow">
            <label for="action_quantity"><?php e(t('quantity')); ?></label>
            <input type="text" name="action_quantity" size="30" value="<?php if (isset($value['action_quantity'])) e($value['action_quantity']); ?>" />
        </div>

        <div class="formrow">
            <label for="action_unit_key"><?php e(t('action unit')); ?></label>
            <select name="action_unit_key">
                <?php foreach ($settings['action_unit'] AS $key => $action_unit): ?>
                    <option value="<?php e($key); ?>" <?php if (isset($value['action_unit_key']) && $value['action_unit_key'] == $key) echo 'selected="selected"'; ?> ><?php e(t($action_unit)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <input type="submit" class="save" name="submit" value="<?php e(t('save', 'common')); ?>" />
    <?php e(t('or', 'common')); ?> <a href="<?php e(url('../')); ?>">
    <?php e(t('cancel', 'common')); ?></a>
</form>