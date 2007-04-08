<?php
/**
 * properties
 *
 * Denne side er en liste over de mulige egenskaber
 * Så skal man kunne klikke sig ind på en egenskab - og så skal man
 * kunne tilknytte mulige egenskaber
 *
 * Eksempel: Her opretter vi plastik som en egenskab
 * På property.php kan man så oprette egenskabsmuligheder.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../include_first.php');

$kernel->useShared('properties');

if (!empty($_REQUEST['product_id']) AND is_numeric($_REQUEST['product_id'])) {
	$object_name = 'Product';
	$module = $kernel->module('product');
	$id = (int)$_REQUEST['product_id'];
	$id_name = 'product_id';
	$redirect = 'product/product';
	$object = new $object_name($kernel, $id);

}
else {
	trigger_error('Der er ikke angivet noget objekt i /shared/keyword/connect.php', FATAL);
}



if (!empty($_POST)) {
}

$redirect = new Redirect($kernel);
$redirect->setDestination('http://www.intraface.dk/shared/keyword/edit.php', 'http://www.intraface.dk/shared/keyword/connect.php?'.$id_name.'='.$object->get('id'));


$page = new Page($kernel);
$page->start("Rediger nøgleord til produkt");

?>
<h1>Rediger egenskabskategorier <?php echo $object->get('name'); ?></h1>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<?php if (count($keywords) > 0): ?>
	<fieldset>
		<legend>Vælg nøgleord</legend>
		<input type="hidden" name="<?php echo $id_name; ?>" value="<?php echo $object->get('id'); ?>" />
		<?php
			$i = 0;
			foreach ($keywords AS $k) {
				if ($i == 0 OR $columnKeywords == $i OR 2 * $columnKeywords == $i) {
					print '<div style="float: left; width: 30%;">';
				}
				print '<input type="checkbox" name="keywordid[]" id="k'.$k['id'].'" value="'.$k['id'].'"';
				if (in_array($k['id'], $checked)) {
					print ' checked="checked" ';
					$selected_keywords .=  $k['keyword'] . ' ';
				}
				print ' />';
				print ' <label for="k'.$k["id"].'"><a href="edit.php?'. $id_name.'='.$object->get('id').'&amp;id='.$k['id'].'">' . $k['keyword'] . ' (#'.$k["id"].')</a></label> - <a href="'.$_SERVER['PHP_SELF'].'?'. $id_name.'='.$object->get('id').'&amp;delete='.$k["id"].'" class="confirm">slet</a><br />'. "\n";
				if (($columnKeywords - 1) == $i OR ((2 * $columnKeywords) - 1) == $i OR $i == $countKeywords - 1) {
					print '</div>';
				}
				$i++;
		}
		?>
		<p style="clear: both; margin-top: 1em;"><br />
			<input type="submit" value="Vælg" name="submit" class="save" /> <input type="submit" value="Vælg og luk" name="close" class="save" />
		</p>
	</fieldset>
	<?php endif; ?>
	<fieldset>
		<legend>Opret nøgleord</legend>
		<p>Du kan oprette flere nøgleord på en gang adskilt med mellemrum, fx <samp>billedbog bamse</samp>. Hvis du vil lave et nøgleord bestående af flere ord, omkranser du dem med anførselstegn <samp>"verdens bedste sune"</samp></p>
		<input type="hidden" name="<?php echo $id_name; ?>" value="<?php echo $object->get('id'); ?>" />
		<label for="keyword">Nøgleord</label>
		<input type="text" name="keyword" id="keyword" value="<?php // echo $selected_keywords; ?>" />
		<input type="submit" value="Gem" name="submit" class="save" />
	</fieldset>
</form>



<?php
$page->end();
?>
