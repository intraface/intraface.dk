
        <table>
            <caption><?php e(t('Pages')); ?></caption>
            <thead>
                <tr>
                    <th><?php e(t('Navigation name')); ?></th>
                    <th><?php e(t('Unique page address')); ?></th>
                    <th><?php e(t('Published')); ?></th>
                    <th><?php e(t('Show')); ?></th>
                    <th colspan="4"></th>
                </tr>
            </thead>
            <?php foreach ($pages as $p) :?>
                <tr>
                    <td><a href="<?php e(url($p['id'])); ?>"><?php e(str_repeat("- ", $p['level']) . $p['navigation_name']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <?php if ($p['status'] == 'published') {
                            echo '&bull;';
} ?>
                    </td>
                    <td>
                        <?php if ($p['status'] == 'published') : // hack siden kan kun vises, hvis den er udgivet. Der b�r laves et eller andet, s� det er muligt anyways - fx en hemmelig kode p� siden ?>
                            <a href="<?php e($p['url']); ?>" target="_blank"><?php e(t('show page')); ?></a>
                        <?php endif; ?>
                    </td>
                    <td class="options">
                        <a class="moveup" href="<?php e(url(null, array('moveup' => $p['id'], 'type' => $type))); ?>"><?php e(t('up')); ?></a>
                        <a class="moveup" href="<?php e(url(null, array('movedown' => $p['id'], 'type' => $type))); ?>"><?php e(t('down')); ?></a>
                        <a class="edit" href="<?php e(url($p['id'], array('edit'))); ?>"><?php e(t('edit settings')); ?></a>
                        <a class="delete" href="<?php e(url($p['id'], array('delete'))); ?>"><?php e(t('delete')); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
