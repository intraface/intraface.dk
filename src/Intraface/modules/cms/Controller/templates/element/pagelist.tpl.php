        <fieldset>
            <legend><?php e(t('page list')); ?></legend>
            <p><?php e(t('page list shows a list with other pages from the cms')); ?></p>
            <div class="formrow">
                <label for="headline"><?php e(t('headline')); ?></label>
                <input type="text" name="headline" id="headline" value="<?php if (!empty($value['headline'])) e($value['headline']); ?>" />
            </div>
            <div class="formrow">
                <label for="no_results"><?php e(t('no results text')); ?></label>
                <input type="text" name="no_results_text" id="no_results" value="<?php if (!empty($value['no_results_text'])) e($value['no_results_text']); ?>" />
            </div>
            <div class="formrow">
                <label for="read_more_text"><?php e(t('read more text')); ?></label>
                <input type="text" name="read_more_text" id="read_more_text" value="<?php if (!empty($value['read_more_text'])) e($value['read_more_text']); ?>" />
            </div>

            <div class="formrow">
                <label for="show_type_id"><?php e(t('show the following pages')); ?></label>
                <select name="show_type" id="show_type_id">
                    <option value="all"<?php if (!empty($value['show_type']) AND $value['show_type'] == 'all') echo ' selected="selected"'; ?>><?php e(t('all pages')); ?></option>
                    <?php foreach ($element->section->cmspage->getTypes() AS $page_type): ?>
                        <option value="<?php e($page_type); ?>"<?php if (isset($value['show_type']) AND $value['show_type'] == $page_type) echo ' selected="selected"'; ?>><?php e(t($page_type)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php
        if (!empty($value['keyword']) && is_array($value['keyword'])) {
            $selected_keywords = $value['keyword'];
        }
        else {
            $selected_keywords = array();
        }
        $keyword = $element->section->cmspage->getKeywordAppender();
        $keywords = $keyword->getUsedKeywords();

        if (count($keywords) > 0) { ?>
            <div><?php e(t('keywords', 'keyword')); ?>: <ul style="display: inline;">
            <?php foreach ($keywords as $v) {
                if (in_array($v['id'], $selected_keywords) === true) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = "";
                }
                ?>
                <li style="display: inline; margin-left: 20px;">
                    <label for="keyword_<?php e($v['id']); ?>">
                    <input type="checkbox" name="keyword[]" value="<?php e($v['id']); ?>" id="keyword_<?php e($v['id']); ?>" <?php e($checked); ?> />
                    &nbsp;<?php e($v['keyword']); ?></label></li>
        <?php
        } ?>
        </ul></div>
        <?php
    }
    ?>
            <!--
            <div class="formrow">
                <label for="lifetime"><?php e(t('lifetime')); ?></label>
                <input type="text" name="lifetime" id="lifetime" value="<?php if (!empty($value['lifetime'])) e($value['lifetime']); ?>" /> <?php e(t('days')); ?> <?php e(t('(empty is forever)')); ?>
            </div>
            -->

        <div class="radio">
                <input type="radio" id="show_headline_only" name="show" value="only_headline" <?php if (!empty($value['show']) AND $value['show'] == 'only_headline') echo ' checked="checked"'; ?> />
                 <label for="show_headline_only"><?php e(t('show only headline')); ?></label>
                 <input type="radio" id="show_all_content" name="show" value="description" <?php if (!empty($value['show']) AND $value['show'] == 'description') echo ' checked="checked"'; ?> />
                <label for="show_all_content"><?php e(t('show the description')); ?></label>
            </div>

        </fieldset>
