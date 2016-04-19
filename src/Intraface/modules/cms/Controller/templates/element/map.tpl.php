        <fieldset>
            <legend><?php e(t('map')); ?></legend>

            <div class="formrow">
                <label><?php e(t('map service')); ?></label>
                <select name="service">
                    <option value=""><?php e(t('choose')); ?></option>
                    <?php foreach ($element->services as $service) : ?>
                    <option value="<?php e($service); ?>"<?php if (!empty($value['service']) and $value['service'] == $service) {
                        echo ' selected="selected"';
} ?>><?php e(t($service)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formrow">
                <label><?php e(t('api key')); ?></label>
                <input type="text" value="<?php if (!empty($value['api_key'])) {
                    e($value['api_key']);
} ?>" name="api_key" />
            </div>

            <div class="formrow">
                <label><?php e(t('map location')); ?></label>
                <input type="text" value="<?php if (!empty($value['text'])) {
                    e($value['text']);
} ?>" name="text" />
            </div>
            <div class="formrow">
                <label><?php e(t('map latitude')); ?></label>
                <input type="text" value="<?php if (!empty($value['latitude'])) {
                    e($value['latitude']);
} ?>" name="latitude" />
            </div>
            <div class="formrow">
                <label><?php e(t('map longitude')); ?></label>
                <input type="text" value="<?php if (!empty($value['longitude'])) {
                    e($value['longitude']);
} ?>" name="longitude" />
            </div>
            <div class="formrow">
                <label><?php e(t('map height')); ?></label>
                <input type="text" value="<?php if (!empty($value['height'])) {
                    e($value['height']);
} ?>" name="height" /> px
            </div>
            <div class="formrow">
                <label><?php e(t('map width')); ?></label>
                <input type="text" value="<?php if (!empty($value['width'])) {
                    e($value['width']);
} ?>" name="width" /> px
            </div>

        </fieldset>
