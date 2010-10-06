<div id="colOne">

    <h1><?php e(t('File')); ?></h1>

    <?php $filemanager->error->view(); ?>

    <ul class="options">
        <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
        <li><a href="<?php e($filemanager->get('file_uri')); ?>"><?php e(t('Get file')); ?></a></li>
        <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close')); ?></a></li>
    </ul>

    <table>
        <caption><?php e(t('Information')); ?></caption>
        <tbody>
        <tr>
            <th><?php e(t('File name')); ?></th>
            <td><?php e($filemanager->get('file_name')); ?></td>
        </tr>
        <tr>
            <th><?php e(t('Created')); ?></th>
            <td><?php e($filemanager->get("dk_date_created")); ?></td>
        </tr>
        <tr>
            <th><?php e(t('File size')); ?></th>
            <td><?php e($filemanager->get("dk_file_size")); ?></td>
        </tr>
        <tr>
            <th><?php e(t('File type')); ?></th>
            <?php
            $file_type = $filemanager->get("file_type");
            ?>
            <td><?php e($file_type['description']); ?></td>
        </tr>
        <tr>
            <th><?php e(t('Accessibility')); ?></th>
            <td><?php e($filemanager->get("accessibility")); ?></td>
        </tr>
        <?php
        if ($filemanager->get('is_image') == 1) {
            ?>
            <tr>
                <th><?php e(t('Image width')); ?></th>
                <td><?php e($filemanager->get('width')); ?>px</td>
            </tr>
            <tr>
                <th><?php e(t('Image height')); ?></th>
                <td><?php e($filemanager->get('height')); ?>px</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <h3><?php e(t('File description')); ?></h3>

    <?php
    if ($filemanager->get('description') == '') {
        ?>
        <p><a href="edit.php?id=<?php print($filemanager->get('id')); ?>"><?php e(t('add description')); ?></a></p>
        <?php
    }
    else {
        print(nl2br($filemanager->get('description')));
    }
    ?>

    <?php
    if ($file_type['image'] == 1) {
        $filemanager->createInstance();
        $instances = $filemanager->instance->getList();

        ?>
        <h3><?php e(t('File sizes')); ?></h3>

        <table class="stribe">
            <thead>
            	<tr>
                <th><?php e(t('Identifier')); ?></th>
                <th><?php e(t('Image width')); ?></th>
                <th><?php e(t('Image height')); ?></th>
                <th><?php e(t('File size')); ?></th>
                <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($instances as $instance) {
                    if ($instance['name'] == 'manual') continue;
                    ?>
                    <tr>
                        <td><a href="<?php e($instance['file_uri']); ?>"><?php e(t($instance['name'], 'filehandler')); ?></a></td>
                        <td><?php e($instance['width']); ?>px</td>
                        <td><?php e($instance['height']); ?>px</td>
                        <td>
                            <?php
                            if (is_numeric($instance['file_size'])) {
                                e(number_format($instance['file_size']/1000, 2, ",", ".")." Kb");
                            }
                            else {
                                e('-');
                            }
                            ?>
                        </td>
                        <td><a href="<?php e(url('crop', array('instance_type' => $instance['name']))); ?>"><?php e(t('custom cropping')); ?></a>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php if ($kernel->user->hasModuleAccess('administration')): ?>
            <?php
            $shared_filehandler = $kernel->useModule('filemanager');
            ?>
            <ul class="options">
                <li><a href="<?php e(url('../sizes')); ?>"><?php e(t('manage your image sizes')); ?></a></li>
            </ul>
        <?php endif; ?>
        <?php
    }
    ?>


</div>


<div id="colTwo">

    <?php
    if ($file_type['image'] == 1) {
        $filemanager->createInstance('system-small');
        ?>
        <div class="box" style="text-align: center;">
            <img src="<?php e($filemanager->instance->get('file_uri')); ?>" alt="" />
        </div>
        <?php
    }
    ?>


    <div id="keywords" class="box">
      <h2><?php e(t('Keywords', 'keyword')); ?></h2>
       <ul class="options">
            <li><a href="<?php e(url('keyword/connect')); ?>"><?php e(t('Add keywords', 'keyword')); ?></a></li>
        </ul>

    <?php
            $context->getKernel()->useShared('keyword');
        $keyword = new Intraface_Keyword_Appender($filemanager);
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) {
            echo '<ul>';
            foreach ($keywords as $k) {
                echo '<li>' . e($k['keyword']) . '</li>';
            }
            echo '</ul>';
        }
    ?>
  </div>

</div>
