<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$element_types = $cms_module->getSetting('element_types');
$translation = $kernel->getTranslation('cms');

if (!empty($_GET['moveto']) AND is_numeric($_GET['moveto'])) {
    $element = CMS_Element::factory($kernel, 'id', $_GET['element_id']);
    if (!is_object($element)) {
        throw new Exception('Unable to create a valid element object');
    }
    $element->getPosition(MDB2::singleton(DB_DSN))->moveToPosition($_GET['moveto']);
    $section = $element->section;
    header('Location: section_html.php?id='.$section->get('id'));
    exit;

} elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $element = CMS_Element::factory($kernel, 'id', $_GET['delete']);
    if (!is_object($element)) {
        throw new Exception('Unable to create a valid element object');
    }
    $element->delete();
    $section = $element->section;
    header('Location: section_html.php?id='.$section->get('id'));
    exit;

} elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
    $element = CMS_Element::factory($kernel, 'id', $_GET['undelete']);
    if (!is_object($element)) {
        throw new Exception('Unable to create a valid element object');
    }
    $element->undelete();
    $section = $element->section;
    header('Location: section_html.php?id='.$section->get('id'));
    exit;
} elseif (!empty($_POST['publish'])) {
    $section = CMS_Section::factory($kernel, 'id', $_POST['id']);
    if ($section->cmspage->publish()) {
        header('location: section_html.php?id='.$section->get('id'));
        exit;
    }
} elseif (!empty($_POST['unpublish'])) {
    $section = CMS_Section::factory($kernel, 'id', $_POST['id']);
    if ($section->cmspage->unpublish()) {
        header('location: section_html.php?id='.$section->get('id'));
        exit;
    }

} elseif (!empty($_POST['add_element'])) {
    $section = CMS_Section::factory($kernel, 'id', $_POST['id']);
    header('Location: section_html_edit.php?section_id='.$section->get('id').'&type='.$_POST['new_element_type_id']);
    exit;
} elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $section = CMS_Section::factory($kernel, 'id', $_GET['id']);
}


$page = new Intraface_Page($kernel);
$page->includeJavascript('global', 'getElementsBySelector.js');
$page->includeJavascript('module', 'section_html.js');
$page->start('CMS');
?>

<h1><?php e($translation->get('edit section')); ?> <?php e($section->get('section_name')); ?> <?php echo e(t('on page')); ?> <?php e($section->cmspage->get('title')); ?></h1>

<ul class="options">
    <?php if (count($section->cmspage->getSections()) > 1): ?>
    <li><a href="page.php?id=<?php e($section->cmspage->get('id')); ?>"><?php e($translation->get('close', 'common')); ?></a></li>
    <?php else: ?>
    <li><a class="edit" href="page_edit.php?id=<?php e($section->cmspage->get('id')); ?>"><?php e($translation->get('edit page settings')); ?></a></li>
    <li><a href="pages.php?type=<?php e($section->cmspage->get('type')); ?>&amp;id=<?php e($section->cmspage->cmssite->get('id')); ?>"><?php e($translation->get('close')); ?></a></li>
    <?php endif; ?>
</ul>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>" id="publish-form">
    <fieldset class="<?php e($section->cmspage->getStatus()); ?>">
    <?php if (!$section->cmspage->isPublished()): ?>
    <?php e('this page is not published'); ?>
    <input type="submit" value="<?php e(t('publish now')); ?>" name="publish" />
    <?php else: ?>
    <?php e('this page is published'); ?>
    <input type="submit" value="<?php e(t('set as draft')); ?>" name="unpublish" />
    <?php endif; ?>
    <input type="hidden" value="<?php e($section->get('id')); ?>" name="id" />
    </fieldset>
</form>

<div class="message">
    <p><?php e(t('this section can contain a number of elements. in the bottom of the page you can add new elements. to edit an element move your mouse over the element, and a yellow box will appear.')); ?></p>
</div>

<div id="cmspage" style="padding: 1em; border: 4px solid #ccc;">
    <?php
        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            echo '<p class="message">Elementet er blevet slettet. <a href="'.basename($_SERVER['PHP_SELF']).'?undelete='.$_GET['delete'].'&amp;id='.$section->cmspage->get('id').'">Fortryd</a>.</p>';
        }

        $html = new CMS_HTML_Parser($translation);
        echo $html->parseElements($section->get('elements'));
    ?>

    <div style="clear: both;"></div>
</div>
<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($section->get('id')); ?>" name="id" />
    <fieldset>
        <legend><?php e($translation->get('create new element')); ?></legend>
        <p><?php e($translation->get('place content on the section by adding elements')); ?></p>
        <select name="new_element_type_id" id="new_element_type_id">
            <option value=""><?php e($translation->get('choose', 'common')); ?></option>
            <?php
                foreach ($element_types AS $key=>$type):
                    if (!in_array($key, $section->template_section->get('allowed_element'))) continue; ?>
                     <option value="<?php e($type); ?>"><?php e($translation->get($type)); ?></option>
                <?php endforeach;
            ?>
        </select>
        <input type="submit" value="<?php e($translation->get('add element')); ?>" name="add_element" />
        <a href="page.php?id=<?php e($section->cmspage->get('id')); ?>"><?php e($translation->get('Cancel', 'common')); ?></a>

    </fieldset>
</form>

<?php
$page->end();
?>