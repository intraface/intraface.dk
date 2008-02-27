<?php
require('../../include_first.php');

$module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // det kunne godt være, at der skulle laves noget så hvis det er første gang
    // man gemmer et template, så ryger man på template.php

    $cmssite = new CMS_Site($kernel, $_POST['site_id']);
    $template = new CMS_Template($cmssite, $_POST['id']);

    if ($template->save($_POST)) {
        if (!empty($_POST['close'])) {
            header('Location: template.php?id='.$template->get('id'));
            exit;
        } else {
            header('Location: template_edit.php?id='.$template->get('id'));
            exit;
        }
    } else {
        $value = $_POST;
    }
} elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $template = CMS_Template::factory($kernel, 'id', $_GET['id']);
    $value = $template->get();

} elseif (!empty($_GET['site_id']) AND is_numeric($_GET['site_id'])) {
    $cmssite = new CMS_Site($kernel, $_GET['site_id']);
    $template = new CMS_Template($cmssite);
    $value['site_id'] = $_GET['site_id'];
} else {
    trigger_error($translation->get('not allowed', 'common'), E_USER_ERROR);
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('edit template')));
?>

<h1><?php e($translation->get('edit template')); ?></h1>

<?php if (!empty($value['id'])): ?>
<ul class="options">
    <li><a href="template.php?id=<?php echo intval($value['id']); ?>"><?php e($translation->get('view template')); ?></a></li>
</ul>
<?php endif; ?>

<?php
    echo $template->error->view($translation);
?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
    <input name="id" type="hidden" value="<?php if (!empty($value['id'])) echo intval($value['id']); ?>" />
    <input name="site_id" type="hidden" value="<?php if (!empty($value['site_id'])) echo intval($value['site_id']); ?>" />

    <fieldset>

        <legend><?php e($translation->get('template')); ?></legend>

        <div class="formrow" id="titlerow">
            <label for="name"><?php e($translation->get('template name')); ?></label>
            <input name="name" type="text" id="name" value="<?php if (!empty($value['name'])) echo safeToForm($value['name']); ?>" size="50" maxlength="255" />
        </div>
        <div class="formrow" id="titlerow">
            <label for="identifier"><?php e($translation->get('identifier', 'common')); ?></label>
            <input name="identifier" type="text" id="name" value="<?php if (!empty($value['identifier'])) echo safeToForm($value['identifier']); ?>" size="50" maxlength="255" />
        </div>

    </fieldset>

    <div style="clear: both;">
        <input type="submit" value="<?php e($translation->get('save', 'common')); ?>" />
        <input type="submit" name="close" value="<?php e($translation->get('save and close', 'common')); ?>" />
        <?php if (!empty($value['id'])): ?>
            <a href="template.php?id=<?php echo intval($value['id']); ?>"><?php e($translation->get('regret')); ?></a>
        <?php else: ?>
            <a href="templates.php?site_id=<?php echo intval($value['site_id']); ?>"><?php e($translation->get('regret', 'common')); ?></a>
        <?php endif; ?>
    </div>
</form>

<?php
$page->end();
?>