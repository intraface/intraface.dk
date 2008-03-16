<?php
require('../../include_first.php');

$kernel->module('newsletter');

$list = new NewsletterList($kernel, $_GET['id']);
$value = $list->get();
$letter = new Newsletter($list);
$letters = $letter->getList();

if (!empty($_GET['delete']) AND is_numeric($_GET['id'])) {
    $letter = new Newsletter($_GET['delete']);
    $letter->delete();
} else {
    $letter = new Newsletter($list);
}



$page = new Page($kernel);
$page->start('Rediger liste');
?>

<h1>Liste</h1>

<ul class="options">
    <li><a class="edit" href="list_edit.php?id=<?php e($list->get('id')); ?>">Ret</a></li>
    <li><a href="letters.php?list_id=<?php e($list->get('id')); ?>">Breve</a></li>
    <?php if($kernel->user->hasModuleAccess('contact')): ?>
        <li><a href="subscribers.php?list_id=<?php e($list->get('id')); ?>">Modtagere</a></li>
    <?php endif; ?>
    <li><a href="index.php">Luk</a></li>
</ul>

<table>
    <caption>Oplysninger om listen</caption>
    <tr>
        <th>Titel</th>
        <td><?php e($value['title']); ?></td>
    </tr>
      <tr>
        <th>Beskrivelse</th>
        <td><?php e($value['description']); ?></td>
    </tr>
    <tr>
        <th>Afsender af e-mailen</th>
        <td><?php e($value['sender_name']); ?> <?php echo htmlspecialchars('<' . $value['reply_email'] . '>'); ?></td>
    </tr>
<!--
    <tr>
        <th>Privatlivspolitik</th>
        <td><?php e($value['privacy_policy']); ?></td>
    </tr>
    <tr>
        <th>Frameldingsbesked</th>
        <td><?php echo nl2br(htmlentities($value['unsubscribe_message'])); ?></td>
    </tr>
-->
    <tr>
        <th>Tilmeldingsbesked</th>
        <td><?php e($value['subscribe_message']); ?></td>
    </tr>
</table>


<?php
$page->end();
?>