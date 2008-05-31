<h1><?php e(t('featured products')); ?></h1>

<table>
    <caption><?php e(t('featured products')); ?></caption>
    <thead>
    <tr>
        <th><?php e(t('Headline')); ?></th>
        <th><?php e(t('Keyword')); ?></th>
        <th></th>
    </tr>
    </thead>
<?php foreach ($all as $feature): ?>
    <tr>
        <td><?php e($feature['headline']); ?></td>
        <td>
        <?php
            $keyword = new Keyword(new Product($kernel), $feature['keyword_id']);
            e($keyword->getKeyword());
        ?>
        </td>
        <td><a href="<?php url(null, array('delete' => $feature['id'])); ?>" class="delete"><?php e(t('Delete')); ?></a></td>
    </tr>
<?php endforeach; ?>
</table>

<form action="<?php e(url(null)); ?>" method="POST">
    <label for="headline">Headline</label> <input id="headline" type="text" name="headline" />
    <label for="keyword_id">Keyword</label>
        <?php
        ?>

    <select id="keyword_id" name="keyword_id">
        <option value=""><?php e(t('Choose')); ?></option>
        <?php foreach ($keywords as $keyword): ?>
        <option value="<?php e($keyword['id']); ?>"><?php e($keyword['keyword']); ?></option>
        <?php endforeach; ?>

    </select>
    <input type="submit" class="save" name="submit" value="<?php e(t('save', 'common')); ?>" />
    <?php e(t('or', 'common')); ?>
    <a href="./"><?php e(t('cancel', 'common')); ?></a>
</form>