<h1><?php e(t('edit section')); ?> <?php e($section->get('section_name')); ?> <?php echo e(t('on page')); ?> <?php e($section->cmspage->get('title')); ?></h1>

<ul class="options">
    <?php if (count($section->cmspage->getSections()) > 1) : ?>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('close')); ?></a></li>
    <?php else : ?>
    <li><a class="edit" href="<?php e(url('../../', array('edit'))); ?>"><?php e(t('edit page settings')); ?></a></li>
    <li><a href="<?php e(url('../../../', array('type' =>$section->cmspage->get('type')))); ?>"><?php e(t('close')); ?></a></li>
    <?php endif; ?>
</ul>

<form method="post" action="<?php e(url()); ?>" id="publish-form">
    <fieldset class="<?php e($section->cmspage->getStatus()); ?>">
    <?php if (!$section->cmspage->isPublished()) : ?>
    <?php e('this page is not published'); ?>
    <input type="submit" value="<?php e(t('publish now')); ?>" name="publish" />
    <?php else : ?>
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
    <?php if (!empty($_GET['delete']) and is_numeric($_GET['delete'])) { ?>
       <p class="message">Elementet er blevet slettet.
       <a href="<?php e(url(null, array('undelete' => $_GET['delete']))); ?>">Fortryd</a>.</p>
    <?php     }

        $html = new CMS_HTML_Parser($translation);
        echo $html->parseElements($section->get('elements'));
    ?>

    <div style="clear: both;"></div>
</div>
<form action="<?php e(url()); ?>" method="post">
    <input type="hidden" value="<?php e($section->get('id')); ?>" name="id" />
    <fieldset>
        <legend><?php e(t('create new element')); ?></legend>
        <p><?php e(t('place content on the section by adding elements')); ?></p>
        <select name="new_element_type_id" id="new_element_type_id">
            <option value=""><?php e(t('choose')); ?></option>
            <?php
            foreach ($element_types as $key => $type) :
                if (!in_array($key, $section->template_section->get('allowed_element'))) {
                    continue;
                } ?>
                     <option value="<?php e($type); ?>"><?php e(t($type)); ?></option>
                <?php                                                                                                                                                                                                                                                                                                                                         endforeach;
            ?>
        </select>
        <input type="submit" value="<?php e(t('add element')); ?>" name="add_element" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>

    </fieldset>
</form>
