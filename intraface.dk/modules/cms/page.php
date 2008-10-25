<?php
require('../../include_first.php');

$module_cms = $kernel->module('cms');
$module_cms->includeFile('HTML_Editor.php');
$translation = $kernel->getTranslation('cms');

$error = array();

if (!empty($_POST)) {

    if (!empty($_POST['publish'])) {
        $cmspage = CMS_Page::factory($kernel, 'id', $_POST['id']);
        if ($cmspage->publish()) {
            header('Location: page.php?id='.$cmspage->get('id'));
            exit;
        }
    } elseif (!empty($_POST['unpublish'])) {
        $cmspage = CMS_Page::factory($kernel, 'id', $_POST['id']);
        if ($cmspage->unpublish()) {
            header('Location: page.php?id='.$cmspage->get('id'));
            exit;
        }
    }

    $files = '';
    if (isset($_POST['section']) && is_array($_POST['section'])) {
        foreach ($_POST['section'] AS $key=>$value) {
            $section = CMS_Section::factory($kernel, 'id', $key);

            if ($section->get('type') == 'picture') {

                if (!empty($_FILES) && !is_array($files)) {
                    $filehandler = new FileHandler($kernel);
                    $filehandler->createUpload();
                    $files = $filehandler->upload->getFiles();
                }

                if (is_array($files)) {
                    foreach ($files AS $file) {
                        if ($file->getProp('form_name') == 'new_picture_'.$key) {

                            $filehandler = new FileHandler($kernel);
                            $filehandler->createUpload();
                            $filehandler->upload->setSetting('file_accessibility', 'public');
                            $pic_id = $filehandler->upload->upload($file);

                            if ($pic_id != 0) {
                                $value['pic_id'] = $pic_id;
                            }

                            // Vi har fundet filen til som passer til dette felt, så er der ikke nogen grund til at køre videre.
                            break;
                        }
                    }
                }

                if (!isset($value['pic_id'])) $value['pic_id'] = 0;
            }
            if (!$section->save($value)) {
                $error[$section->get('id')] = $translation->get('error in section') . ' ' . strtolower(implode($section->error->message, ', '));
            }
        }
    }
    if (empty($error) AND count($error) == 0) {
        if (!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {

            // jeg skal bruge array_key, når der er klikket på choose_file, for den indeholder section_id. Der bør
            // kun kunne være en post i arrayet, så key 0 må være $section_id for vores fil
            $keys = array_keys($_POST['choose_file']);
            $section_id = $keys[0];

            $redirect = Intraface_Redirect::factory($kernel, 'go');
            $module_filemanager = $kernel->useModule('filemanager');
            $redirect->setIdentifier('picture:'.$section_id);
            $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_cms->getPath().'page.php?id='.$section->cmspage->get('id') . '&from_section_id=' . $section_id);

            $redirect->askParameter('file_handler_id');
            header('Location: '.$url);
            exit;
        } elseif (!empty($_POST['edit_html'])) {
            $keys = array_keys($_POST['edit_html']);
            header('Location: section_html.php?id='. $keys[0]);
            exit;
        } elseif (!empty($_POST['close'])) {
            header('Location: pages.php?type='.$section->cmspage->get('type').'&id='.$section->cmspage->cmssite->get('id'));
            exit;
        } else {
            header('Location: page.php?id='.$section->cmspage->get('id'));
            exit;
        }
    } else {
        $cmspage = $section->cmspage;
        $sections = $cmspage->getSections();

        $value = $_POST;
    }
} elseif (!empty($_GET['id'])) {

    if (isset($_GET['return_redirect_id'])) {
        $redirect = Intraface_Redirect::factory($kernel, 'return');
        $identifier_parts = explode(':', $redirect->get('identifier'));
        if ($identifier_parts[0] == 'picture') {
            $section = CMS_Section::factory($kernel, 'id', $identifier_parts[1]);
            $section->save(array('pic_id' => $redirect->getParameter('file_handler_id')));
        }
    }

    $cmspage = CMS_Page::factory($kernel, 'id', $_GET['id']);
    $sections = $cmspage->getSections();

}

// hvis der kun findes et html-element i skabelonen, kan vi lige så godt redigere det som standard.

if (!empty($sections) AND count($sections) == 1 AND array_key_exists(0, $sections) AND $sections[0]->get('type') == 'mixed') {
    header('Location: section_html.php?id='.$sections[0]->get('id'));
    exit;
};


$page = new Intraface_Page($kernel);
if ($kernel->setting->get('user', 'htmleditor') == 'tinymce') {
    $page->includeJavascript('global', 'tinymce/jscripts/tiny_mce/tiny_mce.js');
}
$page->start($translation->get('content on page').' '.$cmspage->get('title'));
?>

<h1><?php e($translation->get('content on page').' '.$cmspage->get('title')); ?></h1>

<ul class="options">
    <li><a class="edit" href="page_edit.php?id=<?php e($cmspage->get('id')); ?>"><?php e($translation->get('edit settings', 'common')); ?></a></li>
    <li><a href="pages.php?type=<?php e($cmspage->get('type')); ?>&amp;id=<?php e($cmspage->cmssite->get('id')); ?>"><?php e($translation->get('close', 'common')); ?></a></li>
    <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
    <li><a href="template.php?id=<?php e($cmspage->get('template_id')); ?>"><?php e($translation->get('edit template')); ?></a></li>
    <?php endif; ?>
</ul>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>" id="publish-form">
    <fieldset class="<?php e($cmspage->getStatus()); ?>">
    <?php if (!$cmspage->isPublished()): ?>
    <?php e(t('this page is not published')); ?>
    <input type="submit" value="<?php e(t('publish now')); ?>" name="publish" />
    <?php else: ?>
    <?php e(t('this page is published')); ?>
    <input type="submit" value="<?php e(t('set as draft')); ?>" name="unpublish" />
    <?php endif; ?>
    <input type="hidden" value="<?php e($_GET['id']); ?>" name="id" />
    </fieldset>
</form>

<br style="clear: both;" />

<?php if (count($sections) == 0): ?>
    <p class="warning">
        <?php echo e($translation->get('no sections added to the template')); ?>
        <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
            <a href="template.php?id=<?php e($cmspage->get('template_id')); ?>"><?php e($translation->get('edit template')); ?></a>.
        <?php else: ?>
            <strong><?php echo e($translation->get('you cannot edit templates')); ?></strong>
        <?php endif; ?>

    </p>
<?php else: ?>

<?php
    if (!empty($error) AND is_array($error) AND array_key_exists($section->get('id'), $error)) {
        echo '<p class="error">'.e($translation->get('error in a section - please see below')).'</p>';
    }
?>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" id="myform">
    <?php $test = ''; foreach ($sections AS $section):  ?>
        <?php
            // hvis value section ikke er sat, så er det en ny post, så vi henter den bare fra section->get()
            if (empty($value['section'][$section->get('id')])) {
                $value['section'][$section->get('id')] = $section->get();
            }
            if (!empty($error) AND is_array($error) AND array_key_exists($section->get('id'), $error)) {
                if (!empty($test) AND $section->get('type') != $test) echo '</fieldset>'; // Udkommenteret af sune, da den gav problemer. </fieldset> udskrives hver gang ny sektion indsættes, derfor kan jeg ikke se hvorfor den skal være der, og det betød at der kom en </fieldset> for meget.
                echo '<p class="error">'.$error[$section->get('id')].$test.$section->get('type').'</p>';
            }

        ?>
        <?php switch($section->get('type')) {
            case 'shorttext':
                if (!array_key_exists($section->get('id'), $error) AND !empty($test) AND $test != 'shorttext') echo '</fieldset>';
                if ($test != 'shorttext') echo '<fieldset>';
                ?>
                <div class="formrow">
                    <label for="section_<?php e($section->get('id')); ?>"><?php e($section->get('section_name')); ?></label>
                    <input id="section_<?php e($section->get('id')); ?>" value="<?php e($value['section'][$section->get('id')]['text']); ?>" name="section[<?php e($section->get('id')); ?>][text]" type="text" maxlength="<?php e($section->template_section->get('size')); ?>" />
                </div>
                <?php
                break;
            case 'longtext':
                if (!array_key_exists($section->get('id'), $error) AND !empty($test) AND $test != 'longtext') echo '</fieldset>';
                if ($test != 'longtext') echo '<fieldset>';
                ?>
                <div class="formrow">
                    <label for="section_<?php e($section->get('id')); ?>"><?php e($section->get('section_name')); ?></label>
                    <?php
                        $editor = new Intraface_modules_cms_HTML_Editor($section->template_section->get('html_format'));
                        $editor->setEditor($kernel->setting->get('user', 'htmleditor'));
                        $textarea_attr = array(
                            //'id' => 'section_'.$section->get('id'),
                            'id' => 'section['.$section->get('id').'][text]', // læg mærke til at ugyldigt id, men nødvendigt, fordi tinymce kræver at id og name er ens for at sende post rigtigt
                            'name' => 'section['.$section->get('id').'][text]',
                            'cols' => 80,
                            'rows' => 10,
                            'class' => 'cms-html-editor'
                        );

                        if ($_SERVER['REQUEST_METHOD'] == 'GET' AND $kernel->setting->get('user', 'htmleditor') == 'tinymce') {
                            $text = $value['section'][$section->get('id')]['html'];
                        }
                        else {
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
                <?php
                /*
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
                */

                break;
            case 'picture':
                if (!array_key_exists($section->get('id'), $error) AND !empty($test)) echo '</fieldset>';
                ?>
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
                $filehandler_html->printFormUploadTag('section['.$section->get('id').'][pic_id]', 'new_picture_'.$section->get('id'), 'choose_file[' . $section->get('id') . ']', array('image_size' => 'small'));
                ?>

                <?php
                break;
                case 'mixed':
                    if (!array_key_exists($section->get('id'), $error)) { ?>
                        </fieldset>
                    <?php } ?>
                    <fieldset>
                        <legend><?php e($section->get('section_name')); ?></legend>
                        <p><?php e($translation->get('there is a html element on the page')); ?></p>
                        <input type="submit" value="<?php e($translation->get('edit section')); ?>" name="edit_html[<?php e($section->get('id')); ?>]" />
                        <p><a class="confirm" title="Dette link forlader siden uden at gemme ændringer på den." href="section_html.php?id=<?php e($section->get('id')); ?>">Rediger det blandede HTML-element &rarr;</a></p>
                <?php
                break;
            ?>

        <?php
            }
            $test = $section->get('type');

            ?>

    <?php endforeach; ?>

    </fieldset>
    <!-- sektionerne kan lige så godt blive vist direkte - på nær html-elementet men hvorfor ikke også html elementet? -->

    <div>
        <input type="submit" value="<?php e($translation->get('save', 'common')); ?>" />
        <input type="submit" name="close" value="<?php e($translation->get('save and close', 'common')); ?>" />
        <a href="pages.php?type=<?php e($cmspage->get('type')); ?>&amp;id=<?php e($cmspage->cmssite->get('id')); ?>"><?php e($translation->get('Cancel', 'common')); ?></a>
    </div>

</form>

<?php endif; ?>


<?php
$page->end();
?>