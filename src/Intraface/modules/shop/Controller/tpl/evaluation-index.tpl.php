<table summary="<?php e(t('Basket evaluation')); ?>" class="stripe">
            <caption><?php e(t('Basket evaluation')); ?></caption>
            <thead>
                <tr>
                    <th><?php e(t('Running index')); ?></th>
                    <th><?php e(t('Evaluation')); ?></th>
                    <th><?php e(t('Action')); ?></th>
                    <th><?php e(t('Go to index after')); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $evaluation) : ?>
                    <tr>
                        <td><?php e($evaluation['running_index']); ?></td>
                        <td><?php
                            e(t('if').' '.t($evaluation['evaluate_target']).' ');
                        if ($evaluation['evaluate_method'] != 'equals') {
                            e(t('is').' ');
                        }
                            e(t($evaluation['evaluate_method']).' '.$evaluation['evaluate_value']);
                        if ($evaluation['evaluate_value_case_sensitive']) { ?>
                                [<acronym title="<?php e(t('case sensitive')); ?>">CS</acronym>]
                                <?php
                        }

                            ?>
                        </td>
                        <td><?php e(t($evaluation['action_action']).' '.$evaluation['action_value'].' '.t('at').' '.$evaluation['action_quantity'].' '.t($evaluation['action_unit'])); ?></td>
                        <td><?php e($evaluation['go_to_index_after']); ?></td>
                        <td>
                            <a href="<?php e(url($evaluation['id'].'/edit')); ?>" class="edit"><?php e(t('Edit')); ?></a>
                            <a href="<?php e(url($evaluation['id'], array('delete'))); ?>" class="delete"><?php e(t('Delete')); ?></a>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><a href="<?php e(url('./edit')); ?>"><?php e(t('Add basket evaluation')); ?></a></p>