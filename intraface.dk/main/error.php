<?php
require('../include_first.php');

$page = new Page($kernel);
$page->start("Fejl");
?>
<h1>Fejl</h1>

<p><?php echo $_GET['msg']; ?></p>

<?php
$page->end();
?>
