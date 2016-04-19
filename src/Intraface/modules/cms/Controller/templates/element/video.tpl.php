        <fieldset>
            <legend><?php e(t('video')); ?></legend>

            <div class="formrow">
                <label><?php e(t('video service')); ?></label>
                <select name="service">
                    <option value=""><?php e(t('choose')); ?></option>
                    <?php foreach ($element->services as $key => $service) : ?>
                    <option value="<?php e($key); ?>"<?php if (!empty($value['service']) and $value['service'] == $key) {
                        echo ' selected="selected"';
} ?>><?php e(t($service)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formrow">
                <label><?php e(t('video id')); ?></label>
                <input type="text" value="<?php if (!empty($value['doc_id'])) {
                    e($value['doc_id']);
} ?>" name="doc_id" />
            </div>
        </fieldset>