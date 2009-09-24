<h1><?php e(t('user preferences')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('close', 'common')); ?></a></li>
</ul>

<form action="<?php e(url()); ?>" method="post">

    <?php echo $context->getError()->view($context->getKernel()->getTranslation()); ?>

    <?php
    /*
    <fieldset id="ptheme" class="radiobuttons">
        <legend>Tema</legend>
      <?php  foreach (themes() AS $key=>$v): ?>
           <label for="<?php e($key); ?>" <?php if ($value['theme'] == $key) echo ' class="selected"'; ?>>
                <input type="radio" id="<?php e($key); ?>" name="theme" value="<?php e($key); ?>" <?php if ($value['theme'] == $key) echo ' checked="checked"'; ?> />
                <?php e($v['name']); ?>. <span><?php e($v['description']); ?></span>
            </label>
         <?php endforeach; ?>
    </fieldset>

    <fieldset id="ptextsize" class="radiobuttons">
        <legend>Tekststørrelse</legend>
            <?php foreach ($textsizes AS $key => $v): ?>
                <label for="<?php e($key); ?>" class="<?php e($key); if ($value['ptextsize'] == $key) echo ' selected'; ?>">
                    <input type="radio" name="ptextsize" id="<?php e($key); ?>" value="<?php e($key); ?>" <?php if ($value['ptextsize'] == $key) echo ' checked="checked"'; ?> /> <?php e($v); ?>
                </label>
            <?php endforeach; ?>
    </fieldset>

    <fieldset>
        <legend>Visning</legend>
        <div>
            <label for="rows_pr_page">Rækker pr. side</label>
            <input type="text" name="rows_pr_page" id="rows_pr_page" value="<?php e($value["rows_pr_page"]); ?>" />
        </div>
    </fieldset>
    */
    ?>
    <fieldset>
        <div class="formrow">
        <label for="language"><?php e(t('language')); ?></label>
        <select name="language" id="language">
            <?php foreach ($context->getKernel()->getTranslation()->getLangs() AS $key => $lang): ?>
                <option value="<?php e($key); ?>"<?php if (!empty($value['language']) AND $value['language'] == $key) echo ' selected="selected"'; ?>><?php e($lang); ?></option>
            <?php endforeach; ?>
        </select>
        </div>

    </fieldset>

    <?php /* if ($kernel->user->hasModuleAccess('cms')): ?>
    <fieldset>
        <legend><?php e(t('which editor do you want to use')); ?></legend>
        <div class="formrow">
        <label><?php e(t('editor')); ?></label>
            <select name="htmleditor">
            <?php foreach ($editors AS $k=>$v) { ?>
                <option value="<?php e($k); ?>"
                    <?php if (!empty($value['htmleditor']) AND $k == $value['htmleditor']) echo ' selected="selected"'; ?>
                    ><?php e($translation->get($v)); ?></option>
            <?php } ?>
            </select>

        </div>
    </fieldset>
    <?php endif; */ ?>


    <fieldset id="labelsize" class="radiobuttons">
        <legend><?php e(t('labels')); ?></legend>
        <p><?php e(t('choose which labels you use - when printing from acrobat reader remember to set page scaling to none')); ?></p>
            <?php foreach ($context->getLabelStandards() AS $key => $v): ?>
                <label for="<?php e($key); ?>" class="<?php e($key); if ($value['label'] == $key) echo ' selected'; ?>">
                    <input type="radio" name="label" id="<?php e($key); ?>" value="<?php e($key); ?>" <?php if ($value['label'] == $key) echo ' checked="checked"'; ?> /> <?php e($v); ?>
                </label>
            <?php endforeach; ?>
    </fieldset>


    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
            eller
        <a href="<?php e(url(null)); ?>"><?php e(t('Cancel', 'common')); ?></a>
    </div>

</form>