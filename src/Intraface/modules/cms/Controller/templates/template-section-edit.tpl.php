<h1><?php e(t('Edit template section')); ?></h1>

<?php

echo $section->error->view($translation);
?>

<form method="post" action="<?php e(url()); ?>"  enctype="multipart/form-data">
    <input name="id" type="hidden" value="<?php e($section->get('id')); ?>" />
    <input name="template_id" type="hidden" value="<?php e($section->template->get('id')); ?>" />
    <input name="type" type="hidden" value="<?php e($section->get('type')); ?>" />
    <fieldset>
        <legend><?php e(t('Information about section')); ?></legend>
        <div class="formrow">
            <label for=""><?php e(t('Template section name')); ?></label>
            <input type="text" name="name" value="<?php if (!empty($value['name'])) e($value['name']); ?>" />
        </div>
        <div class="formrow">
            <label for=""><?php e(t('Identifier', 'common')); ?></label>
            <input type="text" name="identifier" value="<?php  if (!empty($value['identifier'])) e($value['identifier']); ?>" />
        </div>
    </fieldset>

<?php

// disse elementtyper skal svare til en elementtype i en eller anden fil.
switch ($value['type']) {

    case 'shorttext' :
?>
        <fieldset>
            <legend><?php e(t('Information about shorttext')); ?></legend>
            <div class="formrow">
                <label><?php e(t('number of allowed characters - max 255')); ?></label>
                <input name="size" type="text" value="<?php  if (!empty($value['size'])) e($value['size']); ?>" />
            </div>
        </fieldset>
        <?php

        break;

    case 'longtext' :
        if (empty ($value['html_format']))
            $value['html_format'] = array ();
?>
        <fieldset>
            <legend><?php e(t('Information about longtext')); ?></legend>
            <div class="formrow">
                <label><?php e(t('Number of allowed characters')); ?></label>
                <input name="size" type="text" value="<?php if (!empty($value['size'])) e($value['size']); ?>" />
            </div>
        </fieldset>
        <fieldset>
            <legend><?php e(t('Allowed html tags')); ?></legend>
            <?php foreach ($section->getAllowedHTMLOptions() AS $html): ?>
                <input id="html-format-<?php e($html); ?>" type="checkbox" value="<?php e($html); ?>" name="html_format[]" <?php if (in_array($html, $value['html_format'])) echo ' checked="checked"'; ?> />
                <label for="<?php e($html); ?>"><<?php e($html); ?>><?php e(t($html)); ?></<?php e($html); ?>></label>
            <?php endforeach; ?>
        </fieldset>
        <?php

        break;

    case 'picture' :
        $kernel->useShared('filehandler');
        require_once ('Intraface/shared/filehandler/InstanceManager.php');
        $instancemanager = new InstanceManager($kernel);
        $instances = $instancemanager->getList();
?>
        <fieldset>
            <legend><?php e(t('Information about picture')); ?></legend>
            <div class="formrow">
                <label for="pic_size"><?php e(__('Picture size')); ?></label>
                <select name="pic_size">
                    <option value="original"<?php if (!empty($value['pic_size']) AND $value['pic_size'] == 'original') echo ' selected="selected"'; ?>>original</option>
                    <?php foreach ($instances AS $instance): ?>
                    <option value="<?php e($instance['name']); ?>"<?php if (!empty($value['pic_size']) AND $value['pic_size'] == $instance['name']) echo ' selected="selected"'; ?>><?php e(t($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>
        <?php

        break;
    case 'mixed' :
?>
        <fieldset>
            <legend><?php e(t('Mixed allowed elements')); ?></legend>
                <?php

        $element_types = $cms_module->getSetting('element_types');
        foreach ($element_types AS $key => $v): ?>
                <div class="radio">
                <input name="allowed_element[]" type="checkbox" id="allowed_element_<?php e($key); ?>" value="<?php e($key); ?>"

            <?php
            if (isset ($value['allowed_element']) && is_array($value['allowed_element']) AND in_array($key, $value['allowed_element'])) {
                echo ' checked="checked"';
            }
            ?>
            />
            <label for="allowed_element_<?php e($key); ?>"><?php e(__($v)); ?></label>
            </div>
        <?php endforeach; ?>

        </fieldset>
        <?php

        break;

    default :
        trigger_error('"'.$value['type'].'" not allowed', E_USER_ERROR);
        break;

}
?>

    <div class="">
        <input type="submit" value="<?php e(t('Save', 'common')); ?>" />
        <input type="submit" name="close" value="<?php e(t('Save and close', 'common')); ?>" />
        <a href="template.php?id=<?php e($section->template->get('id')); ?>"><?php e(t('Cancel', 'common')); ?></a>
    </div>

</form>