<h1><?php e(t('Change intranet')); ?></h1>

<form action="<?php e(url(null)); ?>" method="get">
    <fieldset id="choose_intranet" class="radiobuttons">
    <legend><?php e(t('choose intranet')); ?></legend>
    <?php foreach ($context->getIntranets() as $id => $name) : ?>
        <label<?php if ($context->getKernel()->intranet->get('id') == $id) {
            echo ' class="selected"';
} ?> for="intranet_<?php e($id); ?>"><input type="radio" name="id" value="<?php e($id); ?>" <?php if ($context->getKernel()->intranet->get('id') == $id) {
    echo ' checked="checked"';
} ?> id="intranet_<?php e($id); ?>" /> <?php e($name); ?></label>
    <?php endforeach; ?>
    </fieldset>
    <div>
        <input type="submit" value="<?php e(t('Switch')); ?>" /> <a href="<?php if (isset($_SERVER['HTTP_REFERER'])) :
            e($_SERVER['HTTP_REFERER']);
else :
    echo 'index.php';
endif; ?>"><?php e(t('Cancel')); ?></a>
    </div>

</form>