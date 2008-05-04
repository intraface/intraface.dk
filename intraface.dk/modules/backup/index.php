<?php
/**
 * Backup
 *
 * Laves efter følgende anvisninger: http://wiki.dreamhost.com/index.php/Automatic_Backup
 *
 * @todo this should be more generic as something which can just execute bash or cronjobs
 *       it should probably work something like the modules just with no database.
 *       So it is possible to register jobs to execute from here.
 *
 * @author Lars Olesen <lars@legestue.net>
 */
require('../../include_first.php');

$module = $kernel->module('backup');
$translation = $kernel->getTranslation('administration');

if (!empty($_POST['mysql'])) {
    if (!exec('bash /home/intraface/backup/mysql.sh')) {
        die('no success');
    }
} elseif (!empty($_POST['domain'])) {
    if (!exec('bash /home/intraface/backup/domain.sh')) {
        die('no success');
    }
}

$page = new Page($kernel);
$page->start('Backup');
?>

<h1>Backup af database</h1>

<form action="index.php" method="post">
    <fieldset>
        <legend>Backup</legend>
        <p><strong>Backup</strong>. På denne side kan du lave en backup af enten filerne i domænet eller systemets databaser. </p>
        <input type="submit" name="mysql" value="Database" />
        <input type="submit" name="domain" value="Filer" />
    </fieldset>
</form>
<?php
/*
<table>
    <caption>Backups</caption>
    <thead>
        <tr>
            <th>Tid</th>
            <th>Filnavn</th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
    <?php
        if (is_dir(BACKUP_PATH) AND ($dh = opendir(BACKUP_PATH))) {
            while (($filename = readdir($dh)) !== false) {
                if (!isset($filename)) continue;
                if ($filename == '.' OR $filename=='..') continue;
    ?>
        <tr>
            <td><?php echo date('d-m-Y H:i:s', filemtime(BACKUP_PATH . '/' . $filename)); ?></td>
            <td><?php echo $filename; ?></td>
            <td><a href="file.php?file=<?php echo $filename; ?>">Download</a></td>
        </tr>
    <?php
            }
            closedir($dh);
        }
    ?>
    </tbody>
</table>
*/?>
<?php
$page->end();
?>
