        <fieldset>
            <legend><?php e(t('del.icio.us')); ?></legend>
            <p><?php e(t('attention: the link has to refer to del.icio.us rss feed')); ?></p>
            <div class="formrow">
                <label><?php e(t('del.icio.us url')); ?></label>
                <input type="text" value="<?php if (!empty($value['url'])) {
                    e($value['url']);
} ?>" name="url" />
            </div>
        </fieldset>