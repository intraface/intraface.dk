<?php
require('../../include_first.php');

$kernel->module('administration');
$translation = $kernel->getTranslation('administration');

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('administration')));
?>
<h1><?php echo safeToHtml($translation->get('administration')); ?></h1>

<p class="message"><?php echo safeToHtml($translation->get('we are making a solution so you can add users yourself')); ?></p>




<?php
$page->end();
?>
