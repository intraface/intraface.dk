    <table summary="<?php e(t('Attribute groups')); ?>" id="attribute_group_table" class="stripe">
        <caption><?php e(t('Attribute groups')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php e(t('Group')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $group) : ?>
                <tr>
                    <td>
                        <input type="checkbox" value="<?php e($group->getId()); ?>" id="product-attribute-<?php e($group->getId()); ?>" <?php if (in_array($group->getId(), $context->existing_groups)) {
                            echo 'checked="checked"';
} ?> name="selected[]" />
                    </td>
                    <td><a href="<?php e(url($group->getId())); ?>"><?php e($group->getName());
                    if ($group->getDescription() != '') {
                        e(' ('.$group->getDescription().')');
                    } ?></a></td>
                    <td class="options"><a class="edit" href="<?php e(url($group->getId(), array('edit'))); ?>"><?php e(t('Edit')); ?></a></td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
