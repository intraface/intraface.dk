<?php
require('../../include_first.php');

$module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_POST)) {
    $cmssite = new CMS_Site($kernel, $_POST['site_id']);
    if ($cmssite->stylesheet->save($_POST)) {
        if (!empty($_POST['close'])) {
            header('Location: index.php?id='.$cmssite->get('id'));
            exit;
        } else {
            header('Location: stylesheet_edit.php?site_id='.$cmssite->get('id'));
            exit;
        }
    } else {
        $value = $_POST;
    }
} else {
    $cmssite = new CMS_Site($kernel, $_GET['site_id']);
    $value['site_id'] = $cmssite->get('id');
    $value['css'] = $cmssite->stylesheet->get('css_own');
}

$page = new Intraface_Page($kernel);
$page->start(__('edit stylesheet'));
?>

<h1><?php e(__('edit stylesheet')); ?></h1>

<ul class="options">
    <li><a href="site.php?id=<?php e($cmssite->get('id')); ?>"><?php e(__('close', 'common')); ?></a></li>
</ul>

<?php
    echo $cmssite->stylesheet->error->view($translation);
?>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>">
    <input name="site_id" type="hidden" value="<?php e($cmssite->get('id')); ?>" />

    <fieldset id="stylesheet">

        <legend><?php e(__('stylesheet')); ?></legend>

        <label for="css">
            <textarea cols="80" rows="20" name="css"><?php e($value['css']); ?></textarea>
         </label>

     </fieldset>

    <div style="clear: both;">
        <input type="submit" value="<?php e(__('save', 'common')); ?>" />
        <input type="submit" name="close" value="<?php e(__('save and close', 'common')); ?>" />
        <a href="site.php?id=<?php e($cmssite->get('id')); ?>"><?php e(__('Cancel', 'common')); ?></a>

    </div>
</form>

<?php
$page->end();
?>