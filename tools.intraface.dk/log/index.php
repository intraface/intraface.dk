<?php 
require('../include_first.php');

$db = &MDB2::singleton(DB_DSN);
if (PEAR::isError($db)) {
	die($db->getMessage());
}

$res = &$db->query("SELECT logtime, ident, message FROM log_table ORDER BY logtime DESC");

?>
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