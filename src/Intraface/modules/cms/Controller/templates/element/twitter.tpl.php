        <fieldset>
            <legend><?php e(t('Twitter')); ?></legend>

            <div class="formrow">
                <label><?php e(t('Search')); ?></label>
                <input type="text" value="<?php if (!empty($value['search'])) {
                    e($value['search']);
} ?>" name="search" />
            </div>
            <div class="formrow">
                <label><?php e(t('Number')); ?></label>
                <input type="text" value="<?php if (!empty($value['number'])) {
                    e($value['number']);
} ?>" name="number" />
            </div>

        </fieldset>