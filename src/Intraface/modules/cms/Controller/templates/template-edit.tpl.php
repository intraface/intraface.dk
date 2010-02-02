<h1><?php e(t('Edit template')); ?></h1>

<?php
    echo $template->error->view($translation);
?>

<form method="post" action="<?php e(url()); ?>">
    <input name="id" type="hidden" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
    <input name="site_id" type="hidden" value="<?php if (!empty($value['site_id'])) e($value['site_id']); ?>" />

    <fieldset>

        <legend><?php e(t('Template')); ?></legend>

        <div class="formrow" id="titlerow">
            <label for="name"><?php e(t('Template name')); ?></label>
            <input name="name" type="text" id="name" value="<?php if (!empty($value['name'])) e($value['name']); ?>" size="50" maxlength="255" />
        </div>
        <div class="formrow" id="titlerow">
            <label for="identifier"><?php e(t('Identifier')); ?></label>
            <input name="identifier" type="text" id="name" value="<?php if (!empty($value['identifier'])) e($value['identifier']); ?>" size="50" maxlength="255" />
        </div>

        <div class="formrow" id="titlerow">
            <label><?php e(t('For page type')); ?></label>
            <?php
            require_once 'Intraface/modules/cms/Page.php';
            $page_types = CMS_Page::getTypesWithBinaryIndex();
            foreach ($page_types AS $key => $page_type): ?>
                <label for="for_page_type_<?php e($key); ?>"><input name="for_page_type[]" type="checkbox" id="for_page_type_<?php e($key); ?>" value="<?php e($key); ?>" <?php if (!empty($value['for_page_type']) && $value['for_page_type'] & $key) echo 'checked="checked"'; ?> /><?php e(t($page_type)); ?></label>
            <?php endforeach; ?>
        </div>

    </fieldset>

    <div style="clear: both;">
        <input type="submit" value="<?php e(t('Save')); ?>" />
        <input type="submit" name="close" value="<?php e(t('Save and close')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>