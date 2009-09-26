<?php
require('../../include_first.php');

$kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
    $section = CMS_TemplateSection::factory($kernel, 'id', $_GET['movedown']);
    $section->getPosition(MDB2::singleton(DB_DSN))->moveDown();
    $template = $section->template;
} elseif (!empty($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
    $section = CMS_TemplateSection::factory($kernel, 'id', $_GET['moveup']);
    $section->getPosition(MDB2::singleton(DB_DSN))->moveUp();
    $template = $section->template;
}

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $section = CMS_TemplateSection::factory($kernel, 'id', $_GET['delete']);
    $section->delete();
    $template = $section->template;
} elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
    $section = CMS_TemplateSection::factory($kernel, 'id', $_GET['undelete']);
    $section->undelete();
    $template = $section->template;
}

if (!empty($_POST['add_section']) AND !empty($_POST['new_section_type'])) {
    $template = CMS_Template::factory($kernel, 'id', $_POST['id']);
    header('Location: template_section_edit.php?template_id='.$template->get('id').'&type='.$_POST['new_section_type']);
    exit;
} elseif (!empty($_POST['add_keywords'])) {
    $shared_keyword = $kernel->useShared('keyword');
    $template = CMS_Template::factory($kernel, 'id', $_POST['id']);
    header('Location: '.$shared_keyword->getPath().'/connect.php?template_id='.$template->get('id'));
    exit;
} elseif (!empty($_REQUEST['id'])) {
    $template = CMS_Template::factory($kernel, 'id', $_REQUEST['id']);
}

$sections = $template->getSections();


$page = new Intraface_Page($kernel);
$page->start(__('template'));
?>

<h1><?php e(__('template')); ?> <?php e($template->get('name')); ?></h1>

<ul class="options">
    <li><a class="edit" href="template_edit.php?id=<?php e($template->get('id')); ?>"><?php e(__('edit', 'common')); ?></a></li>
    <li><a href="templates.php?site_id=<?php e($template->cmssite->get('id')); ?>"><?php e(__('close', 'common')); ?></a></li>
</ul>

    <?php
        if (!empty($_GET['just_deleted']) AND is_numeric($_GET['just_deleted'])) {
            echo '<p class="message">Elementet er blevet slettet. <a href="'.$_SERVER['PHP_SELF'].'?undelete='.$_GET['just_deleted'].'&amp;id='.$cmspage->get('id').'">Fortryd</a>.</p>';
        }
    ?>

<?php if (is_array($sections) AND count($sections) > 0): ?>
    <table>
        <caption><?php e(__('sections')); ?></caption>
        <thead>
            <tr>
                <th><?php e(__('name', 'common')); ?></th>
                <th><?php e(__('identifier', 'common')); ?></th>
                <th><?php e(__('type', 'common')); ?></th>
                <th colspan="4">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($sections AS $s): ?>
        <tr>
            <td><?php e($s['name']); ?></td>
            <td><?php e($s['identifier']); ?></td>
            <td><?php e($s['type']); ?></td>
            <td class="options"><a href="<?php e($_SERVER['PHP_SELF']); ?>?moveup=<?php e($s['id']); ?>"><?php e(__('up','common')); ?></a>
            <a href="<?php e($_SERVER['PHP_SELF']); ?>?movedown=<?php e($s['id']); ?>"><?php e(__('down', 'common')); ?></a>
            <a class="edit" href="template_section_edit.php?id=<?php e($s['id']); ?>"><?php e(__('edit settings', 'common')); ?></a>
            <a class="delete" href="template.php?delete=<?php e($s['id']); ?>"><?php e(__('delete', 'common')); ?></a></td>
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($template->get('id')); ?>" name="id" />
    <fieldset>
        <legend><?php e(__('create section')); ?></legend>
        <select name="new_section_type">
            <option value=""><?php e(__('choose', 'common')); ?></option>
                <option value="shorttext"><?php e(__('shorttext')); ?></option>
                <option value="longtext"><?php e(__('longtext')); ?></option>
                <option value="picture"><?php e(__('picture', 'common')); ?></option>					<option value="mixed"><?php e(__('mixed')); ?></option>
        </select>
        <div>
            <input type="submit" value="<?php e(__('add section')); ?>" name="add_section" />
        </div>
    </fieldset>
    <fieldset>
        <legend><?php e(__('standard keywords')); ?></legend>
        <p><?php e(__('keywords on a template are automatically transferred to the new pages created with the template')); ?></p>
        <input type="submit" value="<?php e(__('add keywords', 'keyword')); ?>" name="add_keywords" />
    </fieldset>
</form>

<?php
$page->end();
?>