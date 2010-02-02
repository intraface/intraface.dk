<h1><?php e(t('Edit stylesheet')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../../')); ?>"><?php e(t('close')); ?></a></li>
</ul>

<?php
    echo $cmssite->stylesheet->error->view($translation);
?>

<form method="post" action="<?php e(url()); ?>">
    <input name="site_id" type="hidden" value="<?php e($cmssite->get('id')); ?>" />

    <fieldset id="stylesheet">

        <legend><?php e(t('Stylesheet')); ?></legend>

        <label for="css">
            <textarea cols="80" rows="20" name="css"><?php e($value['css']); ?></textarea>
         </label>

     </fieldset>

    <div style="clear: both;">
        <input type="submit" value="<?php e(t('save')); ?>" />
        <input type="submit" name="close" value="<?php e(t('save and close')); ?>" />
        <a href="<?php e(url('../../')); ?>"><?php e(t('Cancel')); ?></a>

    </div>
</form>
