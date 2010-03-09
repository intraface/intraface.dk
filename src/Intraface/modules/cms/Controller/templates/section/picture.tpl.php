 <fieldset>
                    <input value="<?php e($section->get('id')); ?>" name="section[<?php e($section->get('id')); ?>][picture]" type="hidden" />

                <legend><?php e($section->get('section_name')); ?></legend>

                <?php
                /*
                if (!empty($_GET['from_section_id']) AND is_numeric($_GET['from_section_id']) AND $_GET['from_section_id'] == $section->get('id')) {
                    $pic_id = $_GET['selected_file_id'];
                    echo '<p class="message">Du har lige valgt en fil fra filmanageren.</p>';
                }
                else {
                    $pic_id = $section->get('pic_id');
                }
                */
                $pic_id = $section->get('pic_id');
                $filehandler = new FileHandler($kernel, $pic_id);
                $filehandler_html = new FileHandlerHTML($filehandler);
                //$filehandler_html->printFormUploadTag('section['.$section->get('id').'][pic_id]', 'new_picture_'.$section->get('id'), 'choose_file[' . $section->get('id') . ']', array('image_size' => 'small'));
                ?>
<img src="<?php e($filehandler->get('file_uri')); ?>" />
<br />
                <input type="submit" value="<?php e(t('Choose file')); ?>" name="choose_file[<?php e($section->get('id')); ?>]" />
