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
