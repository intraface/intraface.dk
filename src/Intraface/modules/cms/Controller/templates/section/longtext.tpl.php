                <div class="formrow">
                    <label for="section_<?php e($section->get('id')); ?>"><?php e($section->get('section_name')); ?></label>
                    <?php
                        $editor = new Intraface_modules_cms_HTML_Editor($section->template_section->get('html_format'));
                        $editor->setEditor($kernel->setting->get('user', 'htmleditor'));
                        $textarea_attr = array(
                            //'id' => 'section_'.$section->get('id'),
                            'id' => 'section['.$section->get('id').'][text]', // l�g m�rke til at ugyldigt id, men n�dvendigt, fordi tinymce kr�ver at id og name er ens for at sende post rigtigt
                            'name' => 'section['.$section->get('id').'][text]',
                            'cols' => 80,
                            'rows' => 10,
                            'class' => 'cms-html-editor'
                        );

                        if ($_SERVER['REQUEST_METHOD'] == 'GET' and $kernel->setting->get('user', 'htmleditor') == 'tinymce') {
                            $text = $value['section'][$section->get('id')]['html'];
                        } else {
                            $text = $value['section'][$section->get('id')]['text'];
                        }

                        echo $editor->get($textarea_attr, $text, array('plugins' => 'save, spellchecker'));
                    ?>

                    <?php
                    /*
                    <textarea class="<?php e($kernel->setting->get('user', 'htmleditor')); ?>" id="section_<?php e($section->get('id')); ?>" name="section[<?php e($section->get('id')); ?>][text]" cols="90" rows="10"><?php e($value['section'][$section->get('id')]['text']); ?></textarea>
                    */
                    ?>

                </div>
                <?php  /*
                $html_format = $section->template_section->get('html_format');
                if (!empty($html_format) AND is_array($html_format)): ?>
                    <script language="javascript" type="text/javascript">
                        // Notice: The simple theme does not use all options some of them are limited to the advanced theme
                        tinyMCE.init({
                            mode : "exact",
                            elements: "section_<?php e($section->get('id')); ?>",
                            theme : "advanced",
                            cleanup : true,
                            verify_html : false,
                            apply_source_formatting : true,
                            relative_urls : false,
                            theme_advanced_toolbar_location : "top",
                            theme_advanced_toolbar_align : "left",
                            theme_advanced_layout_manager : "SimpleLayout",
                            theme_advanced_buttons1 : "<?php echo implode(',', $html_format); ?>",
                            theme_advanced_buttons2 : "",
                            theme_advanced_buttons3 : "",
                            entity_encoding : "raw"
                        });
                    </script>
                <?php endif; ?>
                */?>