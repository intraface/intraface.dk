<form action="<?php e(url(null)); ?>" method="POST">
<table class="stripe">
    <thead>
    <tr>
        <th></th>
        <th><?php e(t('Name')); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($languages as $language) : ?>
    <tr>
        <th><input type="checkbox" value="<?php e($language->getKey()); ?>" name="language[]" <?php if (isset($chosen[$language->getKey()]) and $chosen[$language->getKey()]->getKey() == $language->getKey()) {
            echo ' checked="checked"';
} ?>/></th>
        <th><?php e($language->getDescription()); ?></th>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p><input type="submit" value="<?php e(t('Choose')); ?>" /></p>
</form>