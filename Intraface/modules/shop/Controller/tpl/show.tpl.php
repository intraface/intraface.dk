<h2><?php e(t('Basket evaluations')); ?></h2>

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
                <?php foreach($evaluations AS $evaluation): ?>
                    <tr>
                        <td><?php e($evaluation['running_index']); ?></td>
                        <td><?php
                            e(t('if').' '.t($evaluation['evaluate_target']).' ');
                            if($evaluation['evaluate_method'] != 'equals') {
                                e($translation->get('is').' ');
                            }
                            e(t($evaluation['evaluate_method']).' '.$evaluation['evaluate_value']);
                            if($evaluation['evaluate_value_case_sensitive']) {
                                echo ' [<acronym title="'.e(t('case sensitive')).'">CS</acronym>]';
                            }

                            ?>
                        </td>
                        <td><?php e(t($evaluation['action_action']).' '.$evaluation['action_value'].' '.t('at').' '.$evaluation['action_quantity'].' '.t($evaluation['action_unit'])); ?></td>
                        <td><?php e($evaluation['go_to_index_after']); ?></td>
                        <td>
                            <a href="<?php e(url('basketevaluation', array('id' => $evaluation['id']))); ?>" class="edit"><?php e(t('Edit', 'common')); ?></a>
                            <a href="<?php e(url('basketevaluation', array('delete' => $evaluation['id']))); ?>" class="delete"><?php e(t('Delete', 'common')); ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><a href="<?php e(url('basketevaluation')); ?>"><?php e(t('Add basket evaluation')); ?></a></p>