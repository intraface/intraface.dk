<h1><?php e(t('add keywords to') . ' ' . $object->get('name')); ?></h1>

<?php echo $keyword->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">
    <?php if (is_array($keywords) AND count($keywords) > 0): ?>
    <fieldset>
        <legend><?php e(t('choose keywords')); ?></legend>
        <input type="hidden" name="<?php e($id_name); ?>" value="<?php e($object->get('id')); ?>" />
        <?php
            $i = 0;
            foreach ($keywords AS $k) { ?>
                <input type="checkbox" name="keyword[]" id="k<?php e($k['id']); ?>" value="<?php e($k['id']); ?>"
                <?php
                if (in_array($k['id'], $checked)) {
                    print ' checked="checked" ';
                } ?>
                />
                <label for="k<?php e($k["id"]); ?>">
                	<a href="<?php e(url('../' . $k['id'])); ?>"><?php e($k['keyword']); ?> (#<?php e($k["id"]); ?>)</a></label>
                	- <a href="<?php e(url('../'. $k["id"], array('delete'))); ?>" class="confirm"><?php e(t('delete')); ?></a><br />
        <?php }
        ?>
    </fieldset>
        <div style="clear: both; margin-top: 1em; width:100%;">
            <input type="submit" value="<?php e(t('choose')); ?>" name="submit" class="save" id="submit-save" />
            <input type="submit" value="<?php e(t('choose and close')); ?>" name="close" class="save" id="submit-close" />
        </div>

    <?php endif; ?>
    <fieldset>
        <legend><?php e(t('create keyword')); ?></legend>
        <p><?php e(t('separate keywords by comma')); ?></p>
        <input type="hidden" name="<?php e($id_name); ?>" value="<?php e($object->get('id')); ?>" />
        <label for="keyword"><?php e(t('keywords')); ?></label>
        <input type="text" name="keywords" id="keyword" value="<?php //e($keyword_string); ?>" />
        <input type="submit" value="<?php e(t('save')); ?>" name="submit" id="submit-save-new" />
    </fieldset>
</form>