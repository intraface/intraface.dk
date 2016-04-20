    <fieldset>
            <legend><?php e(t('photo album')); ?></legend>
            <!--
            <div class="formrow">
            <label>Bruger</label>
                <input type="text" value="<?php if (!empty($value['user'])) {
                    e($value['user']);
} ?>" name="user" />
            </div>
            -->
            <!--
            <div class="formrow">
            <label>Tags</label>
                <input type="text" value="<?php if (!empty($value['tags'])) {
                    e($value['tags']);
} ?>" name="tags" />
            </div>
            -->

            <div class="formrow">
                <label><?php e(t('photo album service')); ?></label>
                <select name="service">
                    <option value=""><?php e(t('choose')); ?></option>
                    <?php foreach ($element->services as $key => $service) : ?>
                    <option value="<?php e($key); ?>"<?php if (!empty($value['service']) and $value['service'] == $key) {
                        echo ' selected="selected"';
} ?>><?php e($service); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="formrow">
            <label><?php e(t('photoset id')); ?></label>
                <input type="text" value="<?php if (!empty($value['photoset_id'])) {
                    e($value['photoset_id']);
} ?>" name="photoset_id" />
            </div>
            <!--
            <div class="formrow">
            <label>St�rrelse</label>
                <select name="size">
                    <?php foreach ($element->allowed_sizes as $key => $size) : ?>
                    <option value="<?php e($key); ?>"<?php if (!empty($value['size']) and $value['size'] == $key) {
                        echo ' selected="selected"';
} ?>><?php e(t($size)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            -->


        </fieldset>
