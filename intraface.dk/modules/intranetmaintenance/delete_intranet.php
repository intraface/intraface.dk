<?php
require('../../include_first.php');

$kernel->module('intranetmaintenance');

$allowed_delete = array(
	1 => 'Bambus - VIP-betatest',
	21 => 'Bambus - Lars og Sune',
	22 => 'Bambus - betatest for alle brugere'
);

if (!empty($_POST)) {

	$db = new DB_Sql;
	$db2 = new DB_Sql;

	$intranet_id = intval($_POST['intranet_id']);

	if (!array_key_exists($intranet_id, $allowed_delete)) {
		trigger_error('Du kan kun slette bambus beta og bambus - sune og lars', E_USER_ERROR);
	}

	$db->query("DELETE FROM accounting_account WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_post WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_vat_period WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_voucher WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_voucher_file WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_year WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_year_end WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM accounting_year_end_action WHERE intranet_id = " . $intranet_id);

	// her skulle vi slette noget address

	$db->query("DELETE FROM contact WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM contact_person WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM contact_message WHERE intranet_id  = " . $intranet_id);

	$db->query("DELETE FROM cms_element WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM cms_page WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM cms_parameter WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM cms_section WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM cms_site WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM cms_template WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM cms_template_section WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM comment WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM debtor WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM debtor_item WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM email WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM email_attachment WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM file_handler WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM file_handler_instance WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM filehandler_append_file WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM invoice_payment WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM invoice_reminder WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM invoice_reminder_item WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM invoice_reminder_unpaid_reminder WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM keyword WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM keyword_x_object WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM newsletter_archieve WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM newsletter_list WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM newsletter_subscriber WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM onlinepayment WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM procurement WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM procurement_item WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM product WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM product_detail WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM product_related WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM stock_adaptation WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM stock_regulation WHERE intranet_id = " . $intranet_id);

	$db->query("DELETE FROM todo_list WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM todo_item WHERE intranet_id = " . $intranet_id);
	$db->query("DELETE FROM todo_contact WHERE intranet_id = " . $intranet_id);

	function removeDir($path) {
		// Add trailing slash to $path if one is not there
		if (substr($path, -1, 1) != "/") {
			$path .= "/";
		}

		$normal_files = glob($path . "*");
		$hidden_files = glob($path . "\.?*");
		$all_files = array_merge($normal_files, $hidden_files);

		foreach ($all_files as $file) {
			# Skip pseudo links to current and parent dirs (./ and ../).
			if (preg_match("/(\.|\.\.)$/", $file)) {
               continue;
			}

			if (is_file($file) === TRUE) {
				// Remove each file in this Directory
				unlink($file);
				echo "Removed File: " . $file . "<br>";
			}
			else if (is_dir($file) === TRUE) {
				// If this Directory contains a Subdirectory, run this Function on it
				removeDir($file);
			}
		}
		// Remove Directory once Files have been removed (If Exists)
		if (is_dir($path) === TRUE) {
			rmdir($path);
			//echo "<br>Removed Directory: " . $path . "<br><br>";
		}
	}

	#To remove a dir:
	removeDir('/home/intraface/upload/' . $intranet_id . '/');

}


$page = new Intraface_Page($kernel);
$page->start('Slet intranet');

?>

<h1>Slet intranet</h1>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

	<fieldset>
		<legend>Vælg intranet</legend>
		<select name="intranet_id">
			<option value="">Vælg</option>
			<?php
			foreach ($allowed_delete AS $id=>$intranet):
				echo '<option value="'.$id.'">'.$intranet.'</option>';
			endforeach;
			?>

		</select>
		<input type="submit" value="Slet" />
	</fieldset>

</form>

<?php
echo $page->end();
?>