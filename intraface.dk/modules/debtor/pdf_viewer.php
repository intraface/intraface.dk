<?php
require('../../include_first.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// This is a fairly bad solution! But it is Microsofts fault!!!
	header('Location: http://'.NET_HOST.NET_DIRECTORY.'modules/debtor/pdf.php?id='. intval($_POST['id']));
	exit;
}
else {
	if (!empty($_GET['id'])) {
		$id = (int)$_GET['id'];
	}
	else {
		trigger_error('not a valid id', E_USER_ERROR);
	}
}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="da">
	<head>
		<title>Henter dokument...</title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
			body {
				font-size: 1em;
				font-family: verdana, sans-serif;
			}
			h1 {
				font-size: 1.3em;
			}

			h2 {
				font-size: 1.1em;
			}
		</style>

	</head>
	<body>
		<div id="container">
			<h1>Henter dokument...</h1>

			<p>Dokumentet vil nu blive sendt til din browser.</p>
			<p>Skulle der i opstå problemer med visningen, kan du forsøge at afhjælpe dette ved at følge nedenstående anvisninger.</p>

			<p><a href="" onclick="document.getElementById('help').style.display = 'block'; return false;">Hjælp</a></p>

			<div id="help" style="display:none;">

				<h2>Hjælp til visning af dokument</h2>
				<h3>Vis dokumentet</h3>
				<p>Hvis et dokument bliver blokeret og ikke kan vises, skal du gøre følgende:</p>
				<ol>
					<li>Tryk på bjælken med blokeringsoplysningerne</li>
					<li>Vælg 'Hent fil...'</li>
					<li>Hvis du efterfølgende får vist dialogen 'Filoverførsel' skal du følge vejledning 'Gem dokumentet lokalt'</li>
				</ol>

				<h3>Gem dokumentet lokalt</h3>
				<p>Hvis du får vist dialogen 'Filoverførsel', skal du gøre følgende:</p>
				<ol>
					<li>Tryk på knappen 'Gem' eller 'Gem som' og vælg en placering til dokumentet</li>
					<li>Når dokumentet er hentet kan du åbne det fra hvor du gemte det lokalt på din PC</li>
				</ol>

				<p><b>Bemærk:</b> Af sikkerhedsmæssige årsager vil det ikke altid muligt at vælge 'Åbn' eller 'Kør'. Dette er kun muligt for programmer der er integreret i din browser. For korrekt visning skal du i sådanne tilfælde gemme dokumentet lokalt på din PC og efterfølgende åbne det derfra.</p>

			</div>

			<form id="form-download" method="post" action="<?php e($_SERVER['PHP_SELF']); ?>">
				<input type="hidden" name="id" value="<?php e($id); ?>" />
				<input type="submit" value="Hent" />
			</form>
		</div>

		<script type="text/javascript">
			var form = document.getElementById("form-download");
			form.submit();
		</script>

	</body>
</html>
