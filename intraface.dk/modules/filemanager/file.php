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

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('file')) . ': ' . $filemanager->get('file_name'));

?>

<div id="colOne">

    <h1><?php echo safeToHtml($translation->get('file')); ?></h1>

    <?php $filemanager->error->view(); ?>

    <ul class="options">
        <li><a href="edit.php?id=<?php print($filemanager->get("id")); ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a></li>
        <li><a href="<?php print($filemanager->get('file_uri')); ?>" target="_blank"><?php echo safeToHtml($translation->get('get file')); ?></a></li>
        <li><a href="index.php?use_stored=true"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
    </ul>

    <table>
        <caption><?php echo safeToHtml($translation->get('information')); ?></caption>
        <tbody>
        <tr>
            <th><?php echo safeToHtml($translation->get('file name')); ?></th>
            <td><?php echo safeToHtml($filemanager->get('file_name')); ?></td>
        </tr>
        <tr>
            <th><?php echo safeToHtml($translation->get('created', 'common')); ?></th>
            <td><?php print($filemanager->get("dk_date_created")); ?></td>
        </tr>
        <tr>
            <th><?php echo safeToHtml($translation->get('file size')); ?></th>
            <td><?php print($filemanager->get("dk_file_size")); ?></td>
        </tr>
        <tr>
            <th><?php echo safeToHtml($translation->get('file type')); ?></th>
            <?php
            $file_type = $filemanager->get("file_type");
            ?>
            <td><?php echo safeToHtml($file_type['description']); ?></td>
        </tr>
        <tr>
            <th><?php echo safeToHtml($translation->get('accessibility')); ?></th>
            <td><?php echo safeToHtml($filemanager->get("accessibility")); ?></td>
        </tr>
        <?php
        if($filemanager->get('is_image') == 1) {
            ?>
            <tr>
                <th><?php echo safeToHtml($translation->get('image width')); ?></th>
                <td><?php print($filemanager->get('width')); ?>px</td>
            </tr>
            <tr>
                <th><?php echo safeToHtml($translation->get('image height')); ?></th>
                <td><?php print($filemanager->get('height')); ?>px</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <h3><?php echo safeToHtml($translation->get('file description')); ?></h3>

    <?php
    if($filemanager->get('description') == '') {
        ?>
        <p><a href="edit.php?id=<?php print($filemanager->get('id')); ?>"><?php echo safeToHtml($translation->get('add description')); ?></a></p>
        <?php
    }
    else {
        print(nl2br($filemanager->get('description')));
    }
    ?>

    <?php
    if($file_type['image'] == 1) {
        $filemanager->createInstance();
        $instances = $filemanager->instance->getTypes();

        ?>
        <h3><?php echo safeToHtml($translation->get('file sizes')); ?></h3>

        <table class="stribe">
            <thead>
                <th><?php echo safeToHtml($translation->get('identifier', 'common')); ?></th>
                <th><?php echo safeToHtml($translation->get('image width')); ?></th>
                <th><?php echo safeToHtml($translation->get('image height')); ?></th>
                <th><?php echo safeToHtml($translation->get('file size')); ?></th>
            </thead>
            <tbody>
                <?php
                foreach($instances AS $instance) {
                    if($instance['name'] == 'manual') CONTINUE;
                    ?>
                    <tr>
                        <td><a href="<?php echo safeToHtml($instance['file_uri']); ?>" target="_blank"><?php echo safeToHtml($translation->get($instance['name'], 'filehandler')); ?></a></td>
                        <td><?php echo safeToHtml($instance['width']); ?>px</td>
                        <td><?php echo safeToHtml($instance['height']); ?>px</td>
                        <td>
                            <?php
                            if(is_numeric($instance['file_size'])) {
                                print(number_format($instance['file_size']/1000, 2, ",", ".")." Kb");
                            }
                            else {
                                print('-');
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }
    ?>


</div>


<div id="colTwo">

    <?php
    if($file_type['image'] == 1) {
        $filemanager->createInstance('small');
        ?>
        <div class="box" style="text-align: center;">
            <img src="<?php echo safeToHtml($filemanager->instance->get('file_uri')); ?>" alt="" />
        </div>
        <?php
    }
    ?>


    <div id="keywords" class="box<?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
      <h2><?php echo safeToHtml($translation->get('keywords', 'keyword')); ?></h2>
       <ul class="options">
            <li><a href="<?php echo PATH_WWW; ?>/shared/keyword/connect.php?filemanager_id=<?php echo $filemanager->get('id'); ?>"><?php echo safeToHtml($translation->get('add keywords', 'keyword')); ?></a></li>
        </ul>

    <?php
        $keyword = $filemanager->getKeywords();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) {
            echo '<ul>';
            foreach ($keywords AS $k) {
                echo '<li>' . safeToHtml($k['keyword']) . '</li>';
            }
            echo '</ul>';
        }
    ?>
  </div>

</div>



<?php
$page->end();
?>
