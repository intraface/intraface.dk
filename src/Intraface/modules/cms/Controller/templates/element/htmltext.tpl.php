<fieldset>
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

    ?>

        <textarea id="html-editor" name="text" cols="100" rows="15"><?php if (!empty($value['text'])) e($value['text']); ?></textarea>
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
			    */
if (!isset($value['text'])) $value['text'] = '';
    require_once dirname(__FILE__) . '/../../../CKEditor.php';
    $config['language'] = 'da';
    $config['toolbar'] = array(
         array( 'Source', '-', 'Cut','Copy','Paste','PasteText','PasteFromWord', '', 'Undo','Redo', '-', 'Format', 'Bold', 'Italic', 'Strike' ),
         array( 'Image', 'Link', 'Unlink', 'NumberedList', 'BulletedList', 'Blockquote', 'Outdent', 'Indent', '-', 'Maximize' )
     );
    $config['filebrowserBrowseUrl'] = url('/restricted/module/filemanager/ckeditor', array('images' => 1));
    $CKEditor = new CKEditor();
    echo $CKEditor->editor("text", $value['text'], $config);
    ?>
</fieldset>