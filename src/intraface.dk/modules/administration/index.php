<?php
require('../../include_first.php');

$kernel->module('administration');
$translation = $kernel->getTranslation('administration');

$page = new Intraface_Page($kernel);
$page->start(__('administration'));
?>
<h1><?php e(__('administration')); ?></h1>

<p class="message"><?php e(__('we are making a solution so you can add users yourself')); ?></p>




<?php
$page->end();
?>
