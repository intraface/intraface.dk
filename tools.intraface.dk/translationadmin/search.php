<?php

/**
 * Til at redigere i translation
 *
 *@author: Sune Jensen
 *
 */
require('../include_first.php');

$db = new DB_Sql;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
	<title>Intraface Tools</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">

body {
	font-family: 'Trebuchet MS', Arial;
}

h1 {
	font-size: 1.4em;
}

h2 {
	font-size: 1.2em;
}

div.search {
	border: 3px solid #999999;
	padding: 5px;
	margin-bottom: 20px;
}

table {
	width: 100%;
}

caption {
	background-color: #6666FF;
}

th {
	border-bottom: 3px solid green;
}

td {
	border-bottom: 1px solid black;
}
	
	
	</style>

</head>

<body>

<h1>Søgning</h1>

<p><a href="index.php">Tilbage</a></p>

<div class="search"><form action="search.php" method="GET">Søg: <input type="text" name="search" value="" /> <input type="submit" value=" > " /></form></div> 


<?php
if(isset($_GET['search']) && $_GET['search'] != '') {
	$db->query("SELECT * FROM core_translation_i18n WHERE id LIKE \"%".$_GET['search']."%\"");
	if($db->numRows() > 0) {
		?>
		<table>
			<caption>Søgeresultat</caption>
			<tr>
				<th>Identifier</th>
				<th>Page id</td>
				<th>DK</th>
				<th>UK</th>
				<th></th>
			</tr>
			<?php
			while($db->nextRecord()) {
				?>
				<tr>
					<td><?php echo $db->f('id'); ?></td>
					<td><?php echo $db->f('page_id'); ?></td>
					<td><?php echo $db->f('dk'); ?></td>
					<td><?php echo $db->f('uk'); ?></td>
					<td><a href="index.php?edit_id=<?php echo $db->f('id'); ?>&page_id=<?php echo $db->f('page_id'); ?>">Ret</a></td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}
}
?>
			


</body>
</html>
