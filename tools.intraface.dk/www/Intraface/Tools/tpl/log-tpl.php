<h1>Log</h1>

<table>
    <tr>
        <th>Time</th>
        <th>Identifier</th>
        <th>Message</th>
    </tr>
<?php while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)): ?>
    <tr>
        <td><?php echo $row['logtime']; ?></td>
        <td><?php echo htmlspecialchars($row['ident']); ?></td>
        <td><?php echo htmlspecialchars($row['message']); ?></td>
    </tr>

<?php endwhile; ?>
</table>
