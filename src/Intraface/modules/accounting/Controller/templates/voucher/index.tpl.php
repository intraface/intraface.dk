<h1>Bilag</h1>

<ul class="options">
    <li><a class="excel" href="<?php e(url(null) . '.xls'); ?>" class="new">Poster som excel</a></li>
</ul>

<?php if (count($context->getPosts()) == 0) : ?>
    <p>Der er ikke nogen bilag.</p>
<?php else : ?>
    <table>
        <caption>Bilag</caption>
        <thead>
        <tr>
            <th>Nummer</th>
            <th>Dato</th>
            <th>Tekst</th>
        </tr>
        </thead>
    <?php foreach ($context->getPosts() as $post) : ?>
        <tr>
            <td><a href="<?php e(url($post['id'])); ?>"><?php e($post['number']); ?></a></td>
            <td><?php e($post['date_dk']); ?></td>
            <td><?php e($post['text']); ?></td>
        </tr>

    <?php endforeach; ?>
    </table>

<?php endif; ?>