<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);

$voucher = new Voucher($year);
$posts = $voucher->getList();

$page = new Page($kernel);
$page->start('Regnskab');
?>

<h1>Bilag</h1>

<ul class="options">
    <li><a class="excel" href="posts_excel.php" class="new">Poster som excel</a></li>
</ul>

<?php if (count($posts) == 0): ?>
    <p>Der er ikke nogen bilag.</p>
<?php else: ?>
    <table>
        <caption>Bilag</caption>
        <thead>
        <tr>
            <th>Nummer</th>
            <th>Dato</th>
            <th>Tekst</th>
        </tr>
        </thead>
    <?php foreach ($posts AS $post): ?>
        <tr>
            <td><a href="voucher.php?id=<?php echo $post['id']; ?>"><?php echo $post['number']; ?></a></td>
            <td><?php echo safeToHtml($post['date_dk']); ?></td>
            <td><?php echo safeToHtml($post['text']); ?></td>
        </tr>

    <?php endforeach; ?>
    </table>

<?php endif; ?>

<?php
$page->end();
?>
