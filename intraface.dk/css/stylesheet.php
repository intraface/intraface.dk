<?php
/**
 * This file is used to combine and compress the stylesheets.
 *
 * The reason is that a great speed advantage is gained by making
 * so few HTTP Requests as possible, but we still want to maintain
 * maintainable stylesheets.
 *
 * @author Lars Olesen <lars@legestue.net>
 * @version 1.0.
 */

ob_start('gz_handler');

require '../common.php';

$themes = Intraface_Page::themes();

header('Content-type: text/css');

readfile('reset.css');
readfile('./fontsizes/medium.css');

if (!empty($_GET['theme']) AND array_key_exists($_GET['theme'], $themes) AND is_numeric($_GET['theme'])) {
    readfile('default.css');
    readfile('forms.css');

    $theme = intval($_GET['theme']);
    readfile('skins/' . $themes[$theme]['label'] . '/typo.css');
}

exit;
?>