        <fieldset>
            <legend><?php e(t('File list')); ?></legend>
            <p><?php e(t('File list displays a list with files')); ?></p>

            <?php /* if ($kernel->user->hasModuleAccess('filemanager')): ?>
                <div class="formrow">

                    <input type="radio" name="filelist_select_method" value="keyword" />
                    <strong>N�gleord</strong>
                    <ul style="display: inline;">
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
                </div>
            <?php endif; */ ?>
            <div class="formrow">
                <label for="caption"><?php e(t('Headline')); ?></label>
                <input value="<?php if (!empty($value['caption'])) e($value['caption']); ?>" name="caption" id="caption" type="text" />
            </div>

            <div class="formrow">
                <?php if ($kernel->user->hasModuleAccess('filemanager')): ?>
                    <!-- hvad bruges den her egentlig til? - hvorfor kan man ikke v�lge uden administration -->
                    <input type="hidden" name="filelist_select_method" value="single_file" />
                <?php endif; ?>

                <!--<strong>Enkeltfiler</strong>-->
                <?php

                if (!empty($value['files']) AND is_array($value['files'])) {
                    foreach ($value['files'] AS $file) {
                        $filehandler = new Filehandler($kernel, $file['id']);
                        $filehandlerHTML = new FilehandlerHTML($filehandler);
                        $filehandlerHTML->showFile(url(null, array('delete_filelist_append_file_id' => $file['append_file_id'])));
                        /*
                        ?>
                        <div style="border: 3px solid blue; padding: 5px;"><img src="<?php e($filehandler->instance->get('file_uri')); ?>" width="<?php e($filehandler->instance->get('width')); ?>" height="<?php e($filehandler->instance->get('height')); ?>" /> <a class="delete" href="">Slet</a></div>
                        <?php
                        */
                    }
                }
                ?>

                <?php
                /*
                $filehandler = new Filehandler($kernel);
                $filehandler_html = new FileHandlerHTML($filehandler);
                $filehandler_html->printFormUploadTag('', 'new_file', 'choose_file', array('type' => 'only_upload', 'include_submit_button_name' => 'upload_new'));
                */
                ?>
                <input type="submit" value="Choose files" name="choose_file" />
            </div>

        </fieldset>