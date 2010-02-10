        <fieldset>
            <legend><?php e(t('html text')); ?></legend>
            <label for="cms-html-editor"><?php e(t('html text')); ?></label>
            <br />
            <?php
                // TODO we should tell the user which editor is chosen
                $allowed_html = array('strong', 'em', 'ol', 'ul', 'p', 'h1', 'h2', 'h3', 'h4', 'a', 'blockquote', 'table');
                $editor = new Intraface_modules_cms_HTML_Editor($allowed_html);
                /*
                if (!empty($value['saved_with'])) {
                    $editor->setEditor($value['saved_with']);
                } else {
                    $editor->setEditor($kernel->setting->get('user', 'htmleditor'));
                }
                */
                $editor->setEditor('tinymce');
                if (empty($value['text'])) {$value['text'] = ''; }

                $textarea_attr = array(
                    'id' => 'text',
                    'name' => 'text',
                    'cols' => 120,
                    'rows' => 20,
                    'class' => 'cms-html-editor'
                );

                $editor_configuration = array(
                    'plugins' => array('autosave', 'table', 'save', 'spellchecker', 'paste')
                );


                if ($_SERVER['REQUEST_METHOD'] == 'GET' AND $kernel->setting->get('user', 'htmleditor') == 'tinymce') {
                    if (!isset($value['html'])) $value['html'] = '';
                        $text = $value['html'];
                    }

                    else {
                        if (!isset($value['text'])) $value['text'] = '';
                        $text = $value['text'];
                    }

                echo $editor->get($textarea_attr, $text, $editor_configuration);

            ?>

            <?php
            /*
            <textarea class="<?php echo $kernel->setting->get('user', 'htmleditor'); ?>" id="cms-html-editor" tabindex="1" name="text" cols="100" rows="15" style="width: 100%"><?php if (!empty($value['text'])) e($value['text']); ?></textarea>
            <script language="javascript" type="text/javascript">
                // Notice: The simple theme does not use all options some of them are limited to the advanced theme
                tinyMCE.init({
                    mode : "textareas",
                    theme : "advanced",
                    plugins : "autosave, table, save, spellchecker, paste",
                    cleanup : true,
                    cleanup_on_startup : true,
                    verify_html : false,
                    apply_source_formatting : true,
                    relative_urls : false,
                    entity_encoding : "raw",
                    remove_linebreaks : true,
                    theme_advanced_toolbar_location : "top",
                    theme_advanced_toolbar_align : "left",
                    theme_advanced_layout_manager : "SimpleLayout",
                    theme_advanced_buttons1 : "save, separator, bold, italic, formatselect, separator, bullist,numlist,separator,undo,redo,separator,link,unlink,separator,sub,sup,separator, tablecontrols, separator,charmap,separator,cleanup,code,spellchecker,separator,help,pasteword",
                    theme_advanced_buttons2 : "",
                    theme_advanced_buttons3 : "",
                    theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,h6,blockquote",
                    spellchecker_languages : "+Danish=da, English=en"


                });
            </script>
        */
        ?>
        </fieldset>