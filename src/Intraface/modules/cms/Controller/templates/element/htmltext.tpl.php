<fieldset>
	<legend><?php e(t('HTML-formatted text')); ?></legend>
    <label for="cms-html-editor"><?php e(t('HTML-text')); ?></label>
    <br />
    <?php
        /*
        // TODO we should tell the user which editor is chosen
        $allowed_html = array('strong', 'em', 'ol', 'ul', 'p', 'h1', 'h2', 'h3', 'h4', 'a', 'blockquote', 'table');
        $editor = new Intraface_modules_cms_HTML_Editor($allowed_html);
        $editor->setEditor('tinymce');
        if (empty($value['text'])) {
            $value['text'] = '';
        }

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
            } else {
                if (!isset($value['text'])) {
                    $value['text'] = '';
                }
                $text = $value['text'];
        }

        echo $editor->get($textarea_attr, $text, $editor_configuration);
        */
    ?>

        <textarea class="html-editor" id="html-editor" name="text" cols="100" rows="15" style="width: 100%"><?php if (!empty($value['text'])) e($value['text']); ?></textarea>
			<script type="text/javascript">
			//<![CDATA[
					editor = CKEDITOR.replace( 'html-editor',
						{
							language : 'da',
					        toolbar :
					            [
							        ['Source','Preview'],
							        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'],
							        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
							        ['Image','Table','HorizontalRule','SpecialChar','PageBreak'],
							        '/',
							        ['Format'],
							        ['Bold','Italic','Strike'],
							        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
							        ['Link','Unlink'],
							        ['Maximize','-']
					            ],
						        filebrowserBrowseUrl : '<?php e(url('/restricted/module/filemanager/ckeditor', array('images' => 1))); ?>',
						        //filebrowserUploadUrl : '/uploader/upload.php?type=Files'

						} );


			//]]>
			</script>
</fieldset>