<h1><?php e(t('Shop')); ?></h1>

<ul>
    <li><a href="<?php e(url('create')); ?>"><?php e('Create'); ?></a></li>
</ul>


<table class="stripe">
    <thead>
    <tr>
        <th><?php e(t('Name')); ?></th>
        <th><?php e(t('Identifier')); ?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($shops as $shop): ?>
    <tr>
        <th><a href="<?php e(url($shop->id)); ?>"><?php e($shop->name); ?></a></th>
        <th><?php e($shop->identifier); ?></th>
        <th><a href="<?php e(url($shop->id. '/edit')); ?>"><?php e(t('Edit')); ?></a></th>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>