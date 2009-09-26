<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $template = CMS_Template::factory($kernel, 'id', $_GET['delete']);
    $template->delete();
    $cmssite = $template->cmssite;
} elseif (!empty($_GET['site_id']) AND is_numeric($_GET['site_id'])) {

    $cmssite = new CMS_Site($kernel, $_GET['site_id']);
    $template = new CMS_Template($cmssite);
} else {
    trigger_error('site id has to be set', E_USER_ERROR);
}

$templates = $template->getList();

$page = new Intraface_Page($kernel);
$page->start(__('templates'));
?>

<h1><?php e(__('templates')); ?></h1>

<ul class="options">
    <li><a class="new" href="template_edit.php?site_id=<?php e($cmssite->get('id')); ?>"><?php e(__('create template')); ?></a></li>
    <li><a href="site.php?id=<?php e($cmssite->get('id')); ?>"><?php e(__('close', 'common')); ?></a></li>
</ul>

<?php if (count($templates) == 0): ?>
    <p><?php e(__('no templates found')); ?></p>
<?php else: ?>
<table>
<caption><?php e(__('templates')); ?></caption>
<thead>
<tr>
    <th><?php e(__('template name')); ?></th>
    <th><?php e(__('identifier', 'common')); ?></th>
    <th><?php e(__('for page type')); ?></th>
    <th></th>
</tr>
</thead>
<tbody>
<?php
require_once 'Intraface/modules/cms/Page.php';
$page_types = CMS_Page::getTypesWithBinaryIndex();
?>

<?php foreach ($templates AS $s): ?>
    <tr>
        <td><a href="template.php?id=<?php e($s['id']); ?>"><?php e($s['name']);  ?></a></td>
        <td><?php e($s['identifier']); ?></td>
        <td>
            <?php
            $return = '';
            foreach ($page_types AS $page_key => $page_type){
                if ($page_key & $s['for_page_type']) {
                    if ($return != '') $return .= ', ';
                    $return .= t($page_type);
                }
            }
            e($return);
            ?>

        </td>
        <td class="options">
            <a class="edit" href="template_edit.php?id=<?php e($s['id']); ?>"><?php e(__('edit settings', 'common')); ?></a>
            <a class="delete" href="<?php e($_SERVER['PHP_SELF']); ?>?delete=<?php e($s['id']); ?>"><?php e(__('delete', 'common')); ?></a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<?php
$page->end();
?>
