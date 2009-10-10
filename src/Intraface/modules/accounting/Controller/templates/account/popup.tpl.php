<?php
$accounts = $context->getAccounts();
?>
<head>
	<title>Kontooversigt</title>
	<style type="text/css" media="all">
  	body {
  		margin: 0;
  		padding: 0;
  		font-family: verdana, sans-serif;
  		font-size: 0.8em;
  	}
  	#header {
  		background:  #333;
  		padding: 10px 20px;
  	}
  	#header h1 {
  		padding: 0;
  		margin: 0;
  		color: #ccc;
  		font-family: Georgia, serif;
  		font-weight: normal;
  		font-size: 180%;
  	}
  	#content {
		margin: 0.4em;
		border: 1px solid #ccc;
		padding: 1em 1.8em;
	}
  	table {
		font-size: 1em;
		border-collapse: collapse;
		margin-top: 1em;
	}

	caption {
		background: #ccc;
		padding: 0.4em;
		font-weight: bold;
	}
  	tr.headline {
		font-weight: bold;
		background: #ddd;
		text-align: center;
	}

	th {
		text-align: right;
	}
	th, td {
		padding: 0.2em;
		vertical-align: top;
	}
  	form {
		margin: 0;
		padding: 0;
	}
  	legend {
		margin: 0;
		padding: 0;
	}
  	fieldset {
		padding: 5px;
		margin: 0;
	}
  	legend {
		font-weight: bold;
	}
	</style>

	<script type="text/javascript" src="<?php e(url('/../../javascript/yui/yahoo/yahoo.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/../../javascript/yui/event/event.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/../../javascript/yui/widget/widget.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/../../javascript/getInnerText.js')); ?>"></script>
	<script type="text/javascript" src="<?php e(url('/../../javascript/functions_string.js')); ?>"></script>
	<script type="text/javascript" src="<?php e($context->getAccountingModule()->getPath()); ?>javascript/daybook_list_account.js"></script>
	<?php foreach ($context->getDocument()->styles() as $style): ?>
		<script type="text/javascript" src="<?php e($style); ?>"></script>
	<?php endforeach; ?>

</head>
<body>

	<div id="header">
		<h1>Kontooversigt</h1>
	</div>

	<div id="content">
		<table>
			<caption>Konti</caption>
			<?php foreach ($accounts as $account): ?>
				<?php if ($account['type'] == 'headline'): ?>
					<tr class="headline">
						<td colspan="2"><?php e($account['name']); ?></td>
					</tr>
				<?php else: ?>
					<tr>
						<th><?php e($account['number']); ?></th>
						<td><?php e($account['name']);  ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</table>
	</div>
</body>
</html>