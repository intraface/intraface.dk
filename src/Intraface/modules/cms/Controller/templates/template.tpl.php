<h1><?php e(t('Template')); ?> <?php e($template->get('name')); ?></h1>

<ul class="options">
    <li><a class="edit" href="<?php e(url(null, array('edit'))); ?>"><?php e(t('edit')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('close')); ?></a></li>
</ul>

    <?php
        if (!empty($_GET['just_deleted']) AND is_numeric($_GET['just_deleted'])) {
            echo '<p class="message">Elementet er blevet slettet. <a href="'.$_SERVER['PHP_SELF'].'?undelete='.$_GET['just_deleted'].'&amp;id='.$cmspage->get('id').'">Fortryd</a>.</p>';
        }
    ?>

<?php if (is_array($sections) AND count($sections) > 0): ?>
    <table>
        <caption><?php e(t('Sections')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('Name')); ?></th>
                <th><?php e(t('Identifier')); ?></th>
                <th><?php e(t('Type')); ?></th>
                <th colspan="4">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($sections as $s): ?>
        <tr>
            <td><?php e($s['name']); ?></td>
            <td><?php e($s['identifier']); ?></td>
            <td><?php e($s['type']); ?></td>
            <td class="options"><a href="<?php e(url(null, array('moveup' =>  $s['id']))); ?>"><?php e(t('up')); ?></a>
            <a href="<?php e(url(null, array('movedown' =>  $s['id']))); ?>"><?php e(t('down')); ?></a>
            <a class="edit" href="<?php e(url('edit')); ?>"><?php e(t('edit settings')); ?></a>
            <a class="delete" href="<?php e(url(null, array('delete' =>  $s['id']))); ?>"><?php e(t('delete')); ?></a></td>
        </tr>
    <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


<form action="<?php e(url()); ?>" method="post">
    <input type="hidden" value="put" name="_method" />
    <fieldset>
        <legend><?php e(t('Create section')); ?></legend>
        <select name="new_section_type">
            <option value=""><?php e(t('Choose')); ?></option>
                <option value="shorttext"><?php e(t('shorttext')); ?></option>
                <option value="longtext"><?php e(t('longtext')); ?></option>
                <option value="picture"><?php e(t('picture')); ?></option>
                <option value="mixed"><?php e(t('mixed')); ?></option>
        </select>
        <div>
            <input type="submit" value="<?php e(t('add section')); ?>" name="add_section" />
        </div>
    </fieldset>
    <fieldset>
        <legend><?php e(t('standard keywords')); ?></legend>
        <p><?php e(t('keywords on a template are automatically transferred to the new pages created with the template')); ?></p>
        <input type="submit" value="<?php e(t('add keywords')); ?>" name="add_keywords" />
    </fieldset>
</form>