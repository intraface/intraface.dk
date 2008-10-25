<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

if (isset($_GET['id'])) {
    $id  = intval($_GET['id']);
}
else {
    $id = 0;
}

$filemanager = new FileManager($kernel, $id);

$page = new Intraface_Page($kernel);
$page->start($translation->get('file')) . ': ' . $filemanager->get('file_name');

?>

<div id="colOne">

    <h1><?php e($translation->get('file')); ?></h1>

    <?php echo $filemanager->error->view(); ?>

    <ul class="options">
        <li><a href="edit.php?id=<?php e($filemanager->get("id")); ?>"><?php e($translation->get('edit', 'common')); ?></a></li>
        <li><a href="<?php e($filemanager->get('file_uri')); ?>" target="_blank"><?php e($translation->get('get file')); ?></a></li>
        <li><a href="index.php?use_stored=true"><?php e($translation->get('close', 'common')); ?></a></li>
    </ul>

    <table>
        <caption><?php e($translation->get('information')); ?></caption>
        <tbody>
        <tr>
            <th><?php e($translation->get('file name')); ?></th>
            <td><?php e($filemanager->get('file_name')); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('created', 'common')); ?></th>
            <td><?php e($filemanager->get("dk_date_created")); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('file size')); ?></th>
            <td><?php e($filemanager->get("dk_file_size")); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('file type')); ?></th>
            <?php
            $file_type = $filemanager->get("file_type");
            ?>
            <td><?php e($file_type['description']); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('accessibility')); ?></th>
            <td><?php e($filemanager->get("accessibility")); ?></td>
        </tr>
        <?php
        if ($filemanager->get('is_image') == 1) {
            ?>
            <tr>
                <th><?php e($translation->get('image width')); ?></th>
                <td><?php e($filemanager->get('width')); ?>px</td>
            </tr>
            <tr>
                <th><?php e($translation->get('image height')); ?></th>
                <td><?php e($filemanager->get('height')); ?>px</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <h3><?php e($translation->get('file description')); ?></h3>

    <?php
    if ($filemanager->get('description') == '') {
        ?>
        <p><a href="edit.php?id=<?php e($filemanager->get('id')); ?>"><?php e($translation->get('add description')); ?></a></p>
        <?php
    } else {
        autohtml($filemanager->get('description'));
    }
    ?>

    <?php
    if ($file_type['image'] == 1) {
        $filemanager->createInstance();
        $instances = $filemanager->instance->getList();

        ?>
        <h3><?php e($translation->get('file sizes')); ?></h3>

        <table class="stribe">
            <thead>
                <th><?php e($translation->get('identifier', 'common')); ?></th>
                <th><?php e($translation->get('image width')); ?></th>
                <th><?php e($translation->get('image height')); ?></th>
                <th><?php e($translation->get('file size')); ?></th>
                <th></th>
            </thead>
            <tbody>
                <?php
                foreach ($instances AS $instance) {
                    if ($instance['name'] == 'manual') CONTINUE;
                    ?>
                    <tr>
                        <td><a href="<?php e($instance['file_uri']); ?>" target="_blank"><?php e($translation->get($instance['name'], 'filehandler')); ?></a></td>
                        <td><?php e($instance['width']); ?>px</td>
                        <td><?php e($instance['height']); ?>px</td>
                        <td>
                            <?php
                            if (is_numeric($instance['file_size'])) {
                                e(number_format($instance['file_size']/1000, 2, ",", ".")." Kb");
                            } else {
                                e('-');
                            }
                            ?>
                        </td>
                        <td><a href="crop_image.php?id=<?php e($filemanager->get('id')); ?>&instance_type=<?php e($instance['name']); ?>"><?php e($translation->get('custom cropping')); ?></a>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php if ($kernel->user->hasModuleAccess('administration')): ?>
            <?php
            $shared_filehandler = $kernel->useShared('filehandler');
            ?>
            <ul class="options">
                <li><a href="<?php e($shared_filehandler->getPath()); ?>settings.php"><?php e($translation->get('manage your files sizes')); ?></a></li>
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


    <div id="keywords" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
      <h2><?php e($translation->get('keywords', 'keyword')); ?></h2>
       <ul class="options">
            <li><a href="<?php e(url('/shared/keyword/connect.php', array('filemanager_id' => $filemanager->get('id')))); ?>"><?php e($translation->get('add keywords', 'keyword')); ?></a></li>
        </ul>

    <?php
        $keyword = $filemanager->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) { ?>
            <ul>
            <?php foreach ($keywords AS $k) { ?>
                <li><?php e($k['keyword']); ?></li>
            <?php } ?>
            </ul>
            <?php
        }
    ?>
  </div>

</div>



<?php
$page->end();
?>
