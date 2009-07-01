<?php
require('../../include_first.php');

$kernel->module('administration');
$translation = $kernel->getTranslation('administration');

$page = new Intraface_Page($kernel);
$page->start($translation->get('administration'));
?>
<h1><?php e($translation->get('administration')); ?></h1>

<p class="message"><?php e($translation->get('we are making a solution so you can add users yourself')); ?></p>




<?php
$page->end();
?>
