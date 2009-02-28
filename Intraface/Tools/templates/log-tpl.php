<h1>Log</h1>

<table>
    <tr>
        <th>Time</th>
        <th>Identifier</th>
        <th>Message</th>
    </tr>
<?php while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)): ?>
    <tr>
        <td><?php e($row['logtime']); ?></td>
        <td><?php e($row['ident']); ?></td>
        <td><?php e($row['message']); ?></td>
    </tr>

<?php endwhile; ?>
</table>
