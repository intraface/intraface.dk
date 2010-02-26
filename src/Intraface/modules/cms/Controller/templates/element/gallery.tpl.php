        <fieldset>
            <legend><?php e(t('Photo album')); ?></legend>

            <!-- Egentlig skulle dette bare v�re en, og s� kan man v�lge mellem flickr mv. ogs� m�ske? -->
            <?php /* if ($kernel->user->hasModuleAccess('filemanager')): ?>

            <div class="formrow">

                <input type="radio" name="gallery_select_method" value="keyword" />
                <strong>N�gleord</strong>
                <ul style="display: inline;">
                <!-- <select name="keyword_id" id="keyword_id"> -->
                    <?php

                        $kernel->useModule('filemanager');
                        $filemanager = new FileManager($kernel);
                        $filemanager->getKeywords();
                        $used_keywords = $filemanager->keywords->getUsedKeywords();
                        foreach ($used_keywords AS $k) {

                            echo '<li style="display: inline; margin-left: 20px;"><label for="keyword_'.$k['id'].'"><input type="checkbox" name="keyword_id[]" value="'.$k['id'].'" id="keyword_'.$k['id'].'" ';
                            if ($value['keyword_id']== $k['id']) {
                                echo ' checked="checked"';
                            }
                            echo '/>&nbsp;'.$k['keyword'].'</label></li>';
                        }

                    ?>
                </ul>

                <!-- </select> -->
            </div>
            <?php endif; */ ?>

            <div class="formrow">
                <label for="thumbnail_size"><?php e(t('thumbnail size', 'cms')); ?></label>

                <?php
                $filehandler = new Filehandler($kernel);
                $filehandler->createInstance();
                $instances = $filehandler->instance->getList();
                ?>
                <select name="thumbnail_size">
                    <?php foreach ($instances AS $key => $instance): ?>
                    <option value="<?php e($key); ?>"<?php if (!empty($value['thumbnail_size']) AND $value['thumbnail_size'] == $key) echo ' selected="selected"'; ?>><?php e(t($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="formrow">
                <label for="popup_size"><?php e(t('popup size', 'cms')); ?></label>

                <?php
                $filehandler = new Filehandler($kernel);
                $filehandler->createInstance();
                $instances = $filehandler->instance->getList();

                ?>

                <select name="popup_size">
                    <?php foreach ($instances AS $key => $instance): ?>
                    <option value="<?php e($key); ?>"<?php if (!empty($value['popup_size']) AND $value['popup_size'] == $key) echo ' selected="selected"'; ?>><?php e(t($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="formrow">
                <label for="show_description"><?php e(t('description', 'cms')); ?></label>

                <?php
                $instances = array('show', 'hide');
                ?>

                <select name="show_description">
                    <?php foreach ($instances AS $instance): ?>
                    <option value="<?php e($instance); ?>"<?php if (!empty($value['show_description']) AND $value['show_description'] == $instance) echo ' selected="selected"'; ?>><?php e(t($instance, 'cms')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="formrow">

                <input type="hidden" name="gallery_select_method" value="single_image" />

                <strong><?php e(t('single images')); ?></strong>

                <?php
                if (!empty($value['pictures']) AND is_array($value['pictures'])) {

                    foreach ($value['pictures'] AS $key => $file) {

                        $filehandler = new Filehandler($kernel, $file['id']);
                        $filehandlerHTML = new FilehandlerHTML($filehandler);
                        $filehandlerHTML->showFile(url(null, array('remove_gallery_append_file_id'=>$file['append_file_id'])), array('image_size' => 'small'));

                        // This means that if there is an error in uploading a new file or other fields, the files will be shown anyway.
                        echo '<input type="hidden" name="pictures['.$key.'][id]" value="'.$file['id'].'" />';
                        /*
                        $filehandler->createInstance('small');
                        ?>
                        <div style="border: 3px solid blue; padding: 5px;"><img src="<?php e($file['instances'][2]['file_uri']); ?>" width="<?php e($filehandler->instance->get('width')); ?>" height="<?php e($filehandler->instance->get('height')); ?>" /> <a class="delete" href="section_html_edit.php?id=<?php e($element->get('id')); ?>&delete_gallery_append_file_id=<?php e($file['append_file_id']); ?>">Slet</a></div>
                        <?php
                        */
                    }
                }
                /*
                $filehandler = new Filehandler($kernel);
                $filehandler_html = new FileHandlerHTML($filehandler);
                $filehandler_html->printFormUploadTag('', 'new_pic', 'choose_file', array('type' => 'only_upload', 'include_submit_button_name' => 'upload_new'));
                */

                ?>
                <input type="submit" value="<?php e(t('Add picture')); ?>" name="choose_file" />
                <p><?php e(t('Pictures are sorted by picture name.')); ?></p>
            </div>
        </fieldset>