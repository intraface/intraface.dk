<?php
/**
 * Elementredigering
 *
 * Webinterfacet til de enkelte elementer programmeres alle i denne fil.
 */
require('../../include_first.php');

$module_cms = $kernel->module('cms');
$module_cms->includeFile('HTML_Editor.php');
$shared_filehandler = $kernel->useShared('filehandler');
$shared_filehandler->includeFile('AppendFile.php');
$translation = $kernel->getTranslation('cms');

// saving
if (!empty($_POST)) {


    if (!empty($_POST['id'])) {
        $element = CMS_Element::factory($kernel, 'id', $_POST['id']);
    }
    else {
        $section = CMS_Section::factory($kernel, 'id', $_POST['section_id']);
        $element = CMS_Element::factory($section, 'type', $_POST['type']);
    }


    if($element->get('type') == 'picture') {

        if (!empty($_FILES['new_pic'])) {

            $filehandler = new FileHandler($kernel);
            $filehandler->createUpload();
            $filehandler->upload->setSetting('file_accessibility', 'public');
            $filehandler->upload->setSetting('allow_only_images', 1);
            if($filehandler->upload->isUploadFile('new_pic')) {
                $id = $filehandler->upload->upload('new_pic');
                if($id != 0) {
                    $_POST['pic_id'] = $id;
                }
            }
            $element->error->merge($filehandler->error->getMessage());
        }
    }
    elseif($element->get('type') == 'gallery') {

        if (!empty($_FILES['new_pic']) && isset($_POST['upload_new'])) {

            $filehandler = new FileHandler($kernel);
            $filehandler->createUpload();
            $filehandler->upload->setSetting('file_accessibility', 'public');
            $filehandler->upload->setSetting('allow_only_images', 1);
            $id = $filehandler->upload->upload('new_pic');

            // Newly created element which has not been saved yet.
            if($element->get('id') == 0) {
                $element->save($_POST);
            }

            if($id != 0) {
                $append_file = new AppendFile($kernel, 'cms_element_gallery', $element->get('id'));
                $append_file->addFile($filehandler);
            }
            $element->error->merge($filehandler->error->getMessage());
        }
    }
    elseif($element->get('type') == 'filelist') {

        if (!empty($_FILES['new_file']) && isset($_POST['upload_new'])) {
            $filehandler = new FileHandler($kernel);
            $filehandler->createUpload();
            $filehandler->upload->setSetting('file_accessibility', 'public');
            $id = $filehandler->upload->upload('new_file');

            // Newly created element which has not been saved yet.
            if($element->get('id') == 0) {
                $element->save($_POST);
            }

            if($id != 0) {
                $append_file = new AppendFile($kernel, 'cms_element_filelist', $element->get('id'));
                $append_file->addFile($filehandler);
            }
            $element->error->merge($filehandler->error->getMessage());
        }
    }

    if ($element->save($_POST)) {
        if(!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
            $redirect = Redirect::factory($kernel, 'go');
            $module_filemanager = $kernel->useModule('filemanager');
            if($element->get('type') == 'picture') {
                $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1', $module_cms->getPath().'section_html_edit.php?id='.$element->get('id'));
                $redirect->setIdentifier('picture');
                $redirect->askParameter('file_handler_id');
            }
            elseif($element->get('type') == 'gallery') {
                $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1', $module_cms->getPath().'section_html_edit.php?id='.$element->get('id'));
                $redirect->setIdentifier('gallery');
                $redirect->askParameter('file_handler_id', 'multiple');
            }
            elseif($element->get('type') == 'filelist') {
                $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?', $module_cms->getPath().'section_html_edit.php?id='.$element->get('id'));
                $redirect->setIdentifier('filelist');
                $redirect->askParameter('file_handler_id', 'multiple');
            }
            else {
                trigger_error("Det er ikke en gyldig elementtype til at lave redirect fra", E_USER_ERROR);
            }
            header('Location: '.$url);
            exit;
        }
        elseif (!empty($_POST['close'])) {
            header('Location: section_html.php?id='.$element->section->get('id'));
            exit;
        }
        else {
            header('Location: section_html_edit.php?id='.$element->get('id'));
            exit;
        }
    }
    else {
        $value = $_POST;
    }
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $element = CMS_Element::factory($kernel, 'id', $_GET['id']);
    $value = $element->get();

    // til select - denne kan uden problemer fortrydes ved blot at have et link til samme side
    if (isset($_GET['return_redirect_id'])) {
        $redirect = Redirect::factory($kernel, 'return');
        if($redirect->get('identifier') == 'picture') {
            $value['pic_id'] = $redirect->getParameter('file_handler_id');
        }
        elseif($redirect->get('identifier') == 'gallery') {
            $append_file = new AppendFile($kernel, 'cms_element_gallery', $element->get('id'));
            $array_files = $redirect->getParameter('file_handler_id');
            foreach($array_files AS $file_id) {
                $append_file->addFile(new FileHandler($kernel, $file_id));
            }
            $element->load();
            $value = $element->get();

        }
        elseif($redirect->get('identifier') == 'filelist') {
            $append_file = new AppendFile($kernel, 'cms_element_filelist', $element->get('id'));
            $array_files = $redirect->getParameter('file_handler_id');
            foreach($array_files AS $file_id) {
                $append_file->addFile(new FileHandler($kernel, $file_id));
            }
            $element->load();
            $value = $element->get();
        }
    }

    if(isset($_GET['delete_gallery_append_file_id'])) {

        $append_file = new AppendFile($kernel, 'cms_element_gallery', $element->get('id'));
        $append_file->delete((int)$_GET['delete_gallery_append_file_id']);


        $element->load();
        $value = $element->get();
    }


    if(isset($_GET['delete_filelist_append_file_id'])) {

        $append_file = new AppendFile($kernel, 'cms_element_filelist', $element->get('id'));
        $append_file->delete((int)$_GET['delete_filelist_append_file_id']);

        $element->load();
        $value = $element->get();
    }
}
elseif (!empty($_GET['section_id']) AND is_numeric($_GET['section_id'])) {
    // der skal valideres noget på typen også.

    // FIXME ud fra section bliver cms_site loaded flere gange?
    // formentlig har det noget med Template at gøre
    // i øvrigt er tingene alt for tæt koblet i page
    $section = CMS_Section::factory($kernel, 'id', $_GET['section_id']);
    $element = CMS_Element::factory($section, 'type', $_GET['type']);

    $value = $element->get();

    $value['type'] = $element->get('type');
    $value['page_id'] = $element->get('page_id');
}


$page = new Page($kernel);
if ($kernel->setting->get('user', 'htmleditor') == 'tinymce') {
    $page->includeJavascript('global', 'tiny_mce/tiny_mce.js');
}
$page->start(safeToHtml($translation->get('edit element')));
?>

<h1><?php echo safeToHtml($translation->get('edit element')); ?></h1>

<?php
echo $element->error->view($translation);
?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>"  enctype="multipart/form-data">
    <input name="id" type="hidden" value="<?php echo intval($element->get('id')); ?>" />
    <input name="section_id" type="hidden" value="<?php echo intval($element->section->get('id')); ?>" />
    <input name="type" type="hidden" value="<?php echo $element->get('type'); ?>" />

<?php
// disse elementtyper skal svare til en elementtype i en eller anden fil.

switch ($value['type']) {

    case 'htmltext':
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('html text')); ?></legend>
            <label for="cms-html-editor"><?php echo safeToHtml($translation->get('html text')); ?></label>
            <br />
            <?php
                // TODO we should tell the user which editor is chosen
                $allowed_html = array('strong', 'em', 'ol', 'ul', 'p', 'h1', 'h2', 'h3', 'h4', 'a', 'blockquote', 'table');
                $editor = new HTML_Editor($allowed_html);
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
                    'cols' => 100,
                    'rows' => 15,
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
            <textarea class="<?php echo $kernel->setting->get('user', 'htmleditor'); ?>" id="cms-html-editor" tabindex="1" name="text" cols="100" rows="15" style="width: 100%"><?php if (!empty($value['text'])) echo safeToForm($value['text']); ?></textarea>
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

        <?php
    break;

    case 'wikitext':
        ?>
        <fieldset>
            <legend><?php e($translation->get('html text')); ?></legend>
            <label for="cms-wiki-editor"><?php e($translation->get('wiki text')); ?></label>
            <br />
            <textarea id="cms-wiki-editor" tabindex="1" name="text" cols="100" rows="15" style="width: 100%"><?php if (!empty($value['text'])) e($value['text']); ?></textarea>
        </fieldset>

        <?php
    break;

    case 'picture':
        ?>
        <fieldset>

            <legend><?php echo safeToHtml($translation->get('choose picture', 'common')); ?></legend>

            <?php
                if (empty($value['pic_id'])) $value['pic_id'] = 0;
                $filehandler = new FileHandler($kernel, $value['pic_id']);
                $filehandler_html = new FileHandlerHTML($filehandler);
                $filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));
            ?>
        </fieldset>
        <fieldset>
            <div class="formrow">
                <label for="pic_size"><?php echo safeToHtml($translation->get('size', 'common')); ?></label>

                <?php
                $filehandler = new Filehandler($kernel);
                $filehandler->createInstance();
                $instances = $filehandler->instance->getList();

                ?>

                <select name="pic_size">
                    <option value="original"<?php if (!empty($value['pic_size']) AND $value['pic_size'] == 'original') echo ' selected="selected"'; ?>><?php echo safeToHtml($translation->get('original', 'filehandler')); ?></option>
                    <?php foreach ($instances AS $instance): ?>
                    <option value="<?php echo safeToForm($instance['name']); ?>"<?php if (!empty($value['pic_size']) AND $value['pic_size'] == $instance['name']) echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formrow">
                <label for="pic_text"><?php echo safeToHtml($translation->get('picture text')); ?></label>
                <input name="pic_text" value="<?php if (!empty($value['pic_text'])) echo safeToForm($value['pic_text']); ?>" />
            </div>
            <div class="formrow">
                <label for="pic_url"><?php echo safeToHtml($translation->get('picture url')); ?></label>
                <input name="pic_url" value="<?php if (!empty($value['pic_url'])) echo safeToForm($value['pic_url']); ?>" />
            </div>

        </fieldset>
        <?php
    break;

    case 'pagelist':
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('page list')); ?></legend>
            <p><?php echo safeToHtml($translation->get('page list shows a list with other pages from the cms')); ?></p>
            <div class="formrow">
                <label for="headline"><?php echo safeToHtml($translation->get('headline')); ?></label>
                <input type="text" name="headline" id="headline" value="<?php if (!empty($value['headline'])) echo safeToForm($value['headline']); ?>" />
            </div>
            <div class="formrow">
                <label for="no_results"><?php echo safeToHtml($translation->get('no results text')); ?></label>
                <input type="text" name="no_results_text" id="no_results" value="<?php if (!empty($value['no_results_text'])) echo safeToForm($value['no_results_text']); ?>" />
            </div>
            <div class="formrow">
                <label for="read_more_text"><?php echo safeToHtml($translation->get('read more text')); ?></label>
                <input type="text" name="read_more_text" id="read_more_text" value="<?php if (!empty($value['read_more_text'])) echo safeToForm($value['read_more_text']); ?>" />
            </div>

            <div class="formrow">
                <label for="show_type_id"><?php echo safeToHtml($translation->get('show the following pages')); ?></label>
                <select name="show_type" id="show_type_id">
                    <option value="all"<?php if (!empty($value['show_type']) AND $value['show_type'] == 'all') echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get('all pages')); ?></option>
                    <?php foreach ($element->section->cmspage->getTypes() AS $page_type): ?>
                        <option value="<?php echo $page_type; ?>"<?php if (isset($value['show_type']) AND $value['show_type'] == $page_type) echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get($page_type)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php
        if(isset($value['keyword'])) {
            $selected_keywords = $value['keyword'];
        }
        else {
            $selected_keywords = array();
        }
        $keyword = $element->section->cmspage->getKeywordAppender();
        $keywords = $keyword->getUsedKeywords();

        if(count($keywords) > 0) {
            echo '<div>'. safeToHtml($translation->get('keywords', 'keyword')) . ': <ul style="display: inline;">';
            foreach ($keywords AS $v) {
                if(in_array($v['id'], $selected_keywords) === true) {
                    $checked = 'checked="checked"';
                }
                else {
                    $checked = "";
                }
                echo '<li style="display: inline; margin-left: 20px;"><label for="keyword_'.$v['id'].'"><input type="checkbox" name="keyword[]" value="'.$v['id'].'" id="keyword_'.$v['id'].'" '.$checked.' />&nbsp;'.$v['keyword'].'</label></li>';
        }
        echo '</ul></div>';
    }
    ?>
            <!--
            <div class="formrow">
                <label for="lifetime"><?php echo safeToHtml($translation->get('lifetime')); ?></label>
                <input type="text" name="lifetime" id="lifetime" value="<?php if (!empty($value['lifetime'])) echo safeToForm($value['lifetime']); ?>" /> <?php echo safeToHtml($translation->get('days')); ?> <?php echo safeToHtml($translation->get('(empty is forever)')); ?>
            </div>
            -->

        <div class="radio">
                <input type="radio" id="show_headline_only" name="show" value="only_headline" <?php if (!empty($value['show']) AND $value['show'] == 'only_headline') echo ' checked="checked"'; ?> />
                 <label for="show_headline_only"><?php echo safeToHtml($translation->get('show only headline')); ?></label>
                 <input type="radio" id="show_all_content" name="show" value="description" <?php if (!empty($value['show']) AND $value['show'] == 'description') echo ' checked="checked"'; ?> />
                <label for="show_all_content"><?php echo safeToHtml($translation->get('show the description')); ?></label>
            </div>

        </fieldset>
        <?php
    break;

    case 'filelist':
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('file list')); ?></legend>
            <p><?php echo safeToHtml($translation->get('file list displays a list with files')); ?></p>

            <?php /* if($kernel->user->hasModuleAccess('filemanager')): ?>
                <div class="formrow">

                    <input type="radio" name="filelist_select_method" value="keyword" />
                    <strong>Nï¿½gleord</strong>
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
                <label for="caption"><?php echo safeToHtml($translation->get('headline', 'common')); ?></label>
                <input value="<?php if (!empty($value['caption'])) echo safeToForm($value['caption']); ?>" name="caption" id="caption" type="text" />
            </div>

            <div class="formrow">
                <?php if($kernel->user->hasModuleAccess('filemanager')): ?>
                    <!-- hvad bruges den her egentlig til? - hvorfor kan man ikke vælge uden administration -->
                    <input type="hidden" name="filelist_select_method" value="single_file" />
                <?php endif; ?>

                <!--<strong>Enkeltfiler</strong>-->
                <?php

                // print_r($value);

                if(!empty($value['files']) AND is_array($value['files'])) {
                    foreach($value['files'] AS $file) {
                        $filehandler = new Filehandler($kernel, $file['id']);
                        $filehandlerHTML = new FilehandlerHTML($filehandler);
                        $filehandlerHTML->showFile('section_html_edit.php?id='.$element->get('id').'&delete_filelist_append_file_id='.$file['append_file_id']);
                        /*
                        ?>
                        <div style="border: 3px solid blue; padding: 5px;"><img src="<?php print($filehandler->instance->get('file_uri')); ?>" width="<?php print($filehandler->instance->get('width')); ?>" height="<?php print($filehandler->instance->get('height')); ?>" /> <a class="delete" href="">Slet</a></div>
                        <?php
                        */
                    }
                }
                ?>

                <?php
                $filehandler = new Filehandler($kernel);
                $filehandler_html = new FileHandlerHTML($filehandler);
                $filehandler_html->printFormUploadTag('', 'new_file', 'choose_file', array('type' => 'only_upload', 'include_submit_button_name' => 'upload_new'));
                ?>
            </div>

        </fieldset>
        <?php
    break;

    case 'flickr':
        ?>
    <fieldset>
            <legend><?php echo safeToHtml($translation->get('photo album')); ?></legend>
            <!--
            <div class="formrow">
            <label>Bruger</label>
                <input type="text" value="<?php if (!empty($value['user'])) echo safeToHtml($value['user']); ?>" name="user" />
            </div>
            -->
            <!--
            <div class="formrow">
            <label>Tags</label>
                <input type="text" value="<?php if (!empty($value['tags'])) echo safeToHtml($value['tags']); ?>" name="tags" />
            </div>
            -->

            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('photo album service')); ?></label>
                <select name="service">
                    <option value=""><?php echo safeToHtml($translation->get('choose', 'common')); ?></option>
                    <?php foreach ($element->services AS $key => $service): ?>
                    <option value="<?php echo $key; ?>"<?php if (!empty($value['service']) AND $value['service'] == $key) echo ' selected="selected"'; ?>><?php echo safeToForm($service); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="formrow">
            <label><?php echo safeToHtml($translation->get('photoset id')); ?></label>
                <input type="text" value="<?php if (!empty($value['photoset_id'])) echo safeToForm($value['photoset_id']); ?>" name="photoset_id" />
            </div>
            <!--
            <div class="formrow">
            <label>Stï¿½rrelse</label>
                <select name="size">
                    <?php foreach ($element->allowed_sizes AS $key => $size): ?>
                    <option value="<?php echo $key; ?>"<?php if (!empty($value['size']) AND $value['size'] == $key) echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get($size)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            -->


        </fieldset>
        <?php
    break;

    case 'delicious':
        // hvis der er flere bï¿½r vi ogsï¿½ understï¿½tte dem.
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('del.icio.us')); ?></legend>
            <p><?php echo safeToHtml($translation->get('attention: the link has to refer to del.icio.us rss feed')); ?></p>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('del.icio.us url')); ?></label>
                <input type="text" value="<?php if (!empty($value['url'])) echo safeToForm($value['url']); ?>" name="url" />
            </div>
        </fieldset>
        <?php
    break;
    case 'video':
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('video')); ?></legend>

            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('video service')); ?></label>
                <select name="service">
                    <option value=""><?php echo safeToHtml($translation->get('choose', 'common')); ?></option>
                    <?php foreach ($element->services AS $key => $service): ?>
                    <option value="<?php echo $key; ?>"<?php if (!empty($value['service']) AND $value['service'] == $key) echo ' selected="selected"'; ?>><?php echo safeToHtml($translation->get($service)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('video id')); ?></label>
                <input type="text" value="<?php if (!empty($value['doc_id'])) echo safeToForm($value['doc_id']); ?>" name="doc_id" />
            </div>
        </fieldset>
        <?php
    break;
    case 'map':
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('map')); ?></legend>

            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('map service')); ?></label>
                <select name="service">
                    <option value=""><?php echo safeToHtml($translation->get('choose', 'common')); ?></option>
                    <?php foreach ($element->services AS $service): ?>
                    <option value="<?php echo $service; ?>"<?php if (!empty($value['service']) AND $value['service'] == $service) echo ' selected="selected"'; ?>><?php echo safeToHtml($translation->get($service)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('api key')); ?></label>
                <input type="text" value="<?php if (!empty($value['api_key'])) echo safeToForm($value['api_key']); ?>" name="api_key" />
            </div>

            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('map location')); ?></label>
                <input type="text" value="<?php if (!empty($value['text'])) echo safeToForm($value['text']); ?>" name="text" />
            </div>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('map latitude')); ?></label>
                <input type="text" value="<?php if (!empty($value['latitude'])) echo safeToForm($value['latitude']); ?>" name="latitude" />
            </div>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('map longitude')); ?></label>
                <input type="text" value="<?php if (!empty($value['longitude'])) echo safeToForm($value['longitude']); ?>" name="longitude" />
            </div>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('map height')); ?></label>
                <input type="text" value="<?php if (!empty($value['height'])) echo safeToForm($value['height']); ?>" name="height" /> px
            </div>
            <div class="formrow">
                <label><?php echo safeToHtml($translation->get('map width')); ?></label>
                <input type="text" value="<?php if (!empty($value['width'])) echo safeToForm($value['width']); ?>" name="width" /> px
            </div>

        </fieldset>
        <?php
    break;

    case 'gallery':
        ?>
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('photo album')); ?></legend>

            <!-- Egentlig skulle dette bare vï¿½re en, og sï¿½ kan man vï¿½lge mellem flickr mv. ogsï¿½ mï¿½ske? -->
            <?php /* if($kernel->user->hasModuleAccess('filemanager')): ?>

            <div class="formrow">

                <input type="radio" name="gallery_select_method" value="keyword" />
                <strong>Nï¿½gleord</strong>
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
                <label for="thumbnail_size"><?php echo safeToHtml($translation->get('thumbnail size', 'cms')); ?></label>

                <?php
                $filehandler = new Filehandler($kernel);
                $filehandler->createInstance();
                $instances = $filehandler->instance->getList();
                ?>
                <select name="thumbnail_size">
                    <?php foreach ($instances AS $key => $instance): ?>
                    <option value="<?php echo safeToForm($key); ?>"<?php if (!empty($value['thumbnail_size']) AND $value['thumbnail_size'] == $key) echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="formrow">
                <label for="popup_size"><?php echo safeToHtml($translation->get('popup size', 'cms')); ?></label>

                <?php
                $filehandler = new Filehandler($kernel);
                $filehandler->createInstance();
                $instances = $filehandler->instance->getList();

                ?>

                <select name="popup_size">
                    <?php foreach ($instances AS $key => $instance): ?>
                    <option value="<?php echo safeToForm($key); ?>"<?php if (!empty($value['popup_size']) AND $value['popup_size'] == $key) echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get($instance['name'], 'filehandler')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="formrow">
                <label for="show_description"><?php echo safeToHtml($translation->get('description', 'cms')); ?></label>

                <?php
                $instances = array('show', 'hide');
                ?>

                <select name="show_description">
                    <?php foreach ($instances AS $instance): ?>
                    <option value="<?php echo safeToForm($instance); ?>"<?php if (!empty($value['show_description']) AND $value['show_description'] == $instance) echo ' selected="selected"'; ?>><?php echo safeToForm($translation->get($instance, 'cms')); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="formrow">

                <input type="hidden" name="gallery_select_method" value="single_image" />

                <strong><?php echo safeToHtml($translation->get('single images')); ?></strong>

                <?php

                // print_r($value);

                if(!empty($value['pictures']) AND is_array($value['pictures'])) {

                    foreach($value['pictures'] AS $key => $file) {

                        $filehandler = new Filehandler($kernel, $file['id']);
                        $filehandlerHTML = new FilehandlerHTML($filehandler);
                        $filehandlerHTML->showFile('section_html_edit.php?id='.$element->get('id').'&delete_gallery_append_file_id='.$file['append_file_id'], array('image_size' => 'small'));

                        // This means that if there is an error in uploading a new file or other fields, the files will be shown anyway.
                        echo '<input type="hidden" name="pictures['.$key.'][id]" value="'.$file['id'].'" />';
                        /*
                        $filehandler->createInstance('small');
                        ?>
                        <div style="border: 3px solid blue; padding: 5px;"><img src="<?php print($file['instances'][2]['file_uri']); ?>" width="<?php print($filehandler->instance->get('width')); ?>" height="<?php print($filehandler->instance->get('height')); ?>" /> <a class="delete" href="section_html_edit.php?id=<?php print($element->get('id')); ?>&delete_gallery_append_file_id=<?php print($file['append_file_id']); ?>">Slet</a></div>
                        <?php
                        */
                    }
                }

                $filehandler = new Filehandler($kernel);
                $filehandler_html = new FileHandlerHTML($filehandler);
                $filehandler_html->printFormUploadTag('', 'new_pic', 'choose_file', array('type' => 'only_upload', 'include_submit_button_name' => 'upload_new'));
                ?>
                <p><?php echo $translation->get('Pictures are sorted by picture name.'); ?></p>
            </div>
        </fieldset>
        <?php
    break;

    default:
        trigger_error($translation->get('not a valid type'), E_USER_ERROR);
    break;

}
?>

    <fieldset>
        <legend><?php echo safeToHtml($translation->get('element settings')); ?></legend>

        <div class="formrow">
            <label for="elm-properties"><?php echo safeToHtml($translation->get('element properties')); ?></label>
            <select name="elm_properties">
                <?php foreach($element->properties AS $key => $property): ?>
                <option value="<?php echo $key; ?>"<?php if (!empty($value['elm_properties']) AND $value['elm_properties'] == $key) echo ' selected="selected"'; ?>><?php echo safeToHtml($translation->get($property)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="formrow">
            <label for="elm-adjust"><?php echo safeToHtml($translation->get('element adjustment')); ?></label>
            <select name="elm_adjust">
                <?php foreach($element->alignment AS $key => $alignment): ?>
                <option value="<?php echo $key; ?>"<?php if (!empty($value['elm_adjust']) AND $value['elm_adjust'] == $key) echo ' selected="selected"'; ?>><?php echo safeToHtml($translation->get($alignment, 'cms')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="formrow">
            <label for="elm-width"><?php echo safeToHtml($translation->get('element width')); ?></label>
            <input name="elm_width" id="elm-width" type="text" value="<?php if (!empty($value['elm_width'])) echo safeToHtml($value['elm_width']); ?>" size="3" maxlength="10" /> <?php echo safeToHtml($translation->get('use either %, em or px')); ?>
        </div>


        <div class="radiorow">
            <p>
                <input name="elm_box" id="elm-box" value="box" type="checkbox"<?php if (!empty($value['elm_box']) AND $value['elm_box'] == 'box') echo ' checked="checked"'; ?> /> <label for="elm-box"><?php echo safeToHtml($translation->get('show element in a box')); ?></label>
            </p>
        </div>


    </fieldset>

    <fieldset>
        <legend><?php echo safeToHtml($translation->get('publish settings','cms')); ?></legend>

        <div class="formrow">
            <label for="dateFieldPublish"><?php echo safeToHtml($translation->get('publish date','cms')); ?></label>
            <input name="date_publish" id="dateFieldPublish" type="text" value="<?php if (!empty($value['date_publish'])) echo safeToHtml($value['date_publish']); ?>" size="30" maxlength="225" /> <span id="dateFieldMsg1"><?php echo safeToHtml($translation->get('empty is today')); ?></span>
        </div>

        <div class="formrow">
            <label for="dateFieldExpire"><?php echo safeToHtml($translation->get('expire date','cms')); ?></label>
            <input name="date_expire" id="dateFieldExpire" type="text" value="<?php if (!empty($value['date_expire']))  echo $value['date_expire']; ?>" size="30" maxlength="225" /> <span id="dateFieldMsg2"><?php echo safeToHtml($translation->get('empty never expires')); ?></span>
        </div>
    </fieldset>

    <div class="">
        <input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
        <input type="submit" name="close" value="<?php echo safeToHtml($translation->get('save and close', 'common')); ?>" />
        <a href="section_html.php?id=<?php echo intval($element->section->get('id')); ?>"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
    </div>

</form>

<?php
$page->end();
?>