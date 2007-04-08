<?php
/**
 * @author Sune Jensen <sj@sunet.dk>
 */
require_once('3Party/Cpdf/class.pdf.php');

class PdfMaker extends Cpdf {
	var $value;
	var $page;
	var $kernel;
	var $page_height;
	var $page_width;

	function __construct($kernel) {
		if(!is_object($kernel) || strtolower(get_class($kernel)) != 'kernel') {
			trigger_error("Første parameter er ikke kernel i PdfMaker->__construct", E_USER_ERROR);
		}


		$this->kernel = $kernel;

		$this->page_width = 595;
		$this->page_height = 841;

		// Foruddefineret værdier
		$this->value['margin_top'] = 50;
		$this->value['margin_right'] = 42;
		$this->value["margin_left"] = 42; // Fra 0 til kanten i venstre side
		$this->value["margin_bottom"] = 50;

		$this->value['header_height'] = 51;
		$this->value['header_margin_top'] = 20;
		$this->value['header_margin_bottom'] = 20;

		$this->value["font_size"] = 11;
		$this->value["font_padding_top"] = 1;
		$this->value["font_padding_bottom"] = 4;

		$this->page = 1;

		$this->load();

		// Opretter en nyt A4 dokument
		parent::CorePdf(array(0, 0, $this->page_width, $this->page_height));

	}

	function load() {
		// Sætter værdier på baggrund af faste værdier

		$this->value["right_margin_position"] = $this->page_width - $this->value['margin_right']; // content_width fra 0 til højre-margin
		$this->value["top_margin_position"] = $this->page_height - $this->value['margin_top']; // content_height

		$this->value["content_width"] = $this->page_width - $this->value['margin_right'] - $this->value['margin_left']; // content_width fra 0 til højre-margin
		$this->value["content_height"] = $this->page_height - $this->value['margin_bottom'] - $this->value['margin_top']; // content_height

		$this->value["font_spacing"] = $this->value["font_size"] + $this->value["font_padding_top"] + $this->value["font_padding_bottom"];
	}

	function setValue($key, $value) {
		$this->value[$key] = $value;
	}

	function setX($value) {

		if(is_int($value)) {
			$this->value['x'] = $this->get('margin_left') + $value;
		}
		elseif(is_string($value) && substr($value, 0, 1) == "+") {

			$this->value['x'] +=  intval(substr($value, 1));
		}
		elseif(is_string($value) && substr($value, 0, 1) == "-") {
			$this->value['x'] -= intval(substr($value, 1));
		}
		else {
			trigger_error("Ugyldig værdi i setX: ".$value, FATAL);
		}
	}

	function setY($value) {

		if(is_int($value)) {

			$this->value['y'] = $this->page_height - $this->get('margin_top') - $value;
		}
		elseif(is_string($value) && substr($value, 0, 1) == "+") {
			$this->value['y'] += intval(substr($value, 1));
		}
		elseif(is_string($value) && substr($value, 0, 1) == "-") {

			$this->value['y'] -= intval(substr($value, 1));
		}
		else {
			trigger_error("Ugyldig værdi i setY: ".$value, FATAL);
		}
	}

	function start() {

		// Omskrivning af placering på specielle tegn: æ, ø, å, Æ, Ø, Å
		// Efter Cpdf dokumentation
		// Tabel for tegnenes placering fundet her: http://www.fingertipsoft.com/3dkbd/ansitable.html
		// Tabel for deres navn fundet her: http://www.gust.org.pl/fonty/qx-table2.htm
		// Bemærk at placeringen af tegnene er forskellige fra de 2 tabeller. Den øverste har den rigtige placering.

		$diff = array(230=>'ae', 198=>'AE',
									248=>'oslash', 216=>'Oslash',
									229=>'aring', 197=>'Aring');

		// Hmm her burde lige laves en anden måde at tilgå stien på!
  	$shared_pdf = $this->kernel->useShared('pdf');

		parent::selectFont(PATH_INCLUDE_SHARED.'pdf/fonts/Helvetica.afm', array('differences'=>$diff));

		$this->setX(0);
		$this->setY(0);

  }

	function addHeader($headerImg = "") {

		if(file_exists($headerImg)) {
  		$header = CorePdf::openObject();
			$size = getImageSize($headerImg); // array(0 => width, 1 => height)

			$height = $this->get('header_height');;
			$width = $size[0] * ($height/$size[1]);



			if($width > $this->get('content_width')) {
				$width = $this->get('content_width');
				$height = $size[1] * ($width/$size[0]);
			}
			// die("ff".$width);
			// die($this->get('right_margin_position').' - '.$width.', '.$this->get('top_margin_position').' - '.$height.', '.$width.', '.$height); // , ($this->value["page_width"] - $this->value["margin_left"])/10

			// die($headerImg);
  		CorePdf::addJpegFromFile($headerImg, $this->get('right_margin_position') - $width, $this->page_height - $this->get('header_margin_top') - $height, $width, $height); // , ($this->value["page_width"] - $this->value["margin_left"])/10
  		//
			CorePdf::closeObject();

  		CorePdf::addObject($header, "all");

			$this->setValue('margin_top', $height + $this->get('header_margin_top') + $this->get('header_margin_bottom'));
			$this->setY(0);
  	}

		/*
		if(file_exists($headerImg)) {
  		$header = parent::openObject();
  		parent::addJpegFromFile($headerImg, $this->value["margin_left"], 771, $this->value["page_width"] - $this->value["margin_left"]); // , ($this->value["page_width"] - $this->value["margin_left"])/10
  		parent::closeObject();

  		parent::addObject($header, "all");
  	}
		*/

	}

	function roundRectangle($x, $y, $width, $height, $round) {
		parent::setLineStyle(1);
		parent::line($x, $y+$round, $x, $y+$height-$round);
		parent::line($x+$round, $y+$height, $x+$width-$round, $y+$height);
		parent::line($x+$width, $y+$height-$round, $x+$width, $y+$round-1);
		parent::line($x+$width-$round, $y, $x+$round, $y);

		parent::partEllipse($x+$round, $y+$round,180, 270, $round);
		parent::partEllipse($x+$round, $y+$height-$round, 90, 180, $round);
		parent::partEllipse($x+$width-$round, $y+$height-$round, 0, 90, $round);
		parent::partEllipse($x+$width-$round, $y+$round, 270, 360, $round);

	}

	function writeDocument($data, $filnavn) {
		//$file = fopen("files/".$filnavn, "wb");
		$file = fopen($filnavn, "wb");
		fwrite($file, $data);
		fclose($file);
	}

	/**
	 *
	 */

	function nextPage($sub_text = false) {

		if($sub_text == true) {
			parent::addText($this->value["right_margin_position"] - parent::getTextWidth($this->value["font_size"], "<i>Fortsættes på næste side...</i>") - 30, $this->value["margin_bottom"] - $this->value['font_padding_top'] - $this->value['font_size'], $this->value["font_size"], "<i>Fortsættes på næste side...</i>");
		}
		parent::newPage();
		$this->setY(0);
		// $pointY = $this->value["page_height"] - 30;	// lige lidt afstand på næste side til starten
		$this->page++;
		return $this->get('y');
	}


	function get($key = '') {
		if(!empty($key)) {
			return($this->value[$key]);
		}
		else {
			return $this->value;
		}
	}


	/**
	 * @param customer: Customer objekt
	 * @param title: title på fakturaen f.eks. Faktura
	 * @param docinfo: array med linjer der skal stå i documentinfo kassen: array(array("label" => "Dato", "value" => "10-20-2004"), ...);
	 * @param attention_to: att på modtager addressen.
	 */
	/*
	Hører ikke til her
	function addRecieverAndSender($pointY, $customer, $intranet = array(), $title = "", $docinfo = array()) {

		$pointX = $this->get("margin_left");

		if(!is_array($customer)) {
			trigger_error("customer skal være et array", FATAL);
		}

		if(!is_object($customer["object"]->address)) {
			trigger_error("Customer mangler address", FATAL);
		}

		if(isset($intranet["address_id"])) {
			$intranet_address = new Address("intranet", $this->kernel->intranet->get("id"), $intranet["address_id"]);
		}
		else {
			$intranet_address = $this->kernel->intranet->address;
		}

		$box_top = $pointY;
		$box_padding_top = 2; // mellemrum fra top boks til første linie
		$box_padding_bottom = 9;
		$box_width = 275; // ($page_width - $margin_left - 10)/2;
		$box_height = $this->get("font_spacing") * 10 + $box_padding_top + $box_padding_bottom;
		$box_small_height = $this->get("font_spacing") * 3 + $box_padding * 2 + 2;

		// Udskrivning af modtager

		$pointY -= $box_padding_top;
		$pointY -= $this->get("font_spacing");
		parent::addText($pointX + 10, $pointY, $this->get("font_size"), "<b>".$customer["object"]->address->get("name")."</b>");
		parent::addText($pointX + $box_width - 40, $pointY + 4, $this->get("font_size") - 4, "Modtager");
		$pointY -= $this->get("font_spacing");

		if($customer["attention_to"] != "") {
			CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "Att: ".$customer["attention_to"]);
			$pointY -= $this->get("font_spacing");
		}

		// elseif($customer["object"]->address->get("contactname") != "" && $customer["object"]->address->get("contactname") != $customer["object"]->address->get("name")) {
		//	CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "Att: ".$customer["object"]->address->get("contactname"));
		// 	$pointY -= $this->get("font_spacing");
		// }

		$line = explode("\r\n", $customer["object"]->address->get("address"));
		for($i = 0; $i < count($line); $i++) {
			CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), $line[$i]);
			$pointY -= $this->get("font_spacing");
			if($i == 2) $i = count($line);
		}
		// $pointY -= $this->get("font_spacing");
		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), $customer["object"]->address->get("postcode")." ".$customer["object"]->address->get("city"));
		$pointY -= $this->get("font_spacing") * 2;

		if($customer["object"]->address->get("cvr") != "") {
			CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "CVR.:");
			CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $customer["object"]->address->get("cvr"));
			$pointY -= $this->get("font_spacing");
		}
		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "Kundenr.:");
		CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $customer["object"]->get("number"));
		if ($customer["object"]->address->get("ean")) {
		 CorePdf::addText($pointX + 10, $pointY - 15, $this->get("font_size"), "EANnr.:");
		 CorePdf::addText($pointX + 10 + 60, $pointY - 15, $this->get("font_size"), $customer["object"]->address->get("ean"));
		}

		$pointY = $box_top - $box_height;
		$this->roundRectangle($pointX, $pointY, $box_width, $box_height, 10);

		// Udskrivning af Afsender data

		$pointX = $box_width + $this->get("margin_left") + 10;
		$pointY = $box_top;
		$pointY -= $box_padding_top;
		$pointY -= $this->get("font_spacing");

		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "<b>".$intranet_address->get("name")."</b>");
		CorePdf::addText($this->get("page_width") - 40, $pointY + 4, $this->get("font_size") - 4, "Afsender");
		$pointY -= $this->get("font_spacing");
		$line = explode("\r\n", $intranet_address->get("address"));
		for($i = 0; $i < count($line); $i++) {
			CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), $line[$i]);
			$pointY -= $this->get("font_spacing");
			if($i == 2) $i = count($line);
		}
		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), $intranet_address->get("postcode")." ".$intranet_address->get("city"));
		$pointY -= $this->get("font_spacing") * 2;

		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "CVR.:");
		CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $intranet_address->get("cvr"));
		$pointY -= $this->get("font_spacing");

		if($intranet["user_id"] != 0) {
			$user = new User($intranet["user_id"]);
			CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "Kontakt:");
			CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $user->address->get("name"));
		}

		$pointY -= $this->get("font_spacing");
		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "Telefon:");
		if(get_class($user) == "user" && $user->address->get("phone") != "") {
			CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $user->address->get("phone"));
		}
		else {
			CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $intranet_address->get("phone"));
		}

		$pointY -= $this->get("font_spacing");
		CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), "E-mail:");
		if(get_class($user) == "user" && $user->address->get("email") != "") {
			CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $user->address->get("email"));
		}
		else {
			CorePdf::addText($pointX + 10 + 60, $pointY, $this->get("font_size"), $intranet_address->get("email"));
		}

		$pointY = $box_top - $box_height;
		$this->roundRectangle($pointX, $pointY, $this->get("page_width") - $pointX, $box_height, 10);

		// Udskrvining af fakturadata

		if(is_array($docinfo) && count($docinfo) > 0) {
			$pointY -= 10;
			$box_small_top = $pointY;
			$box_small_height = count($docinfo) * $this->get("font_spacing") + $box_padding_top + $box_padding_bottom;
 			$pointY -= $box_padding_top;

			for($i = 0; $i < count($docinfo); $i++) {
				$pointY -= $this->get("font_spacing");
				CorePdf::addText($pointX + 10, $pointY, $this->get("font_size"), $docinfo[$i]["label"]);
				CorePdf::addText($this->get("page_width") - 40 - $this->getTextWidth($this->get("font_size"), $docinfo[$i]["value"]), $pointY, $this->get("font_size"), $docinfo[$i]["value"]);
			}

			$pointY = $box_small_top - $box_small_height;
			$this->roundRectangle($pointX, $pointY, $this->get("page_width") - $pointX, $box_small_height, 10);
		}
		else {
			$pointY = $this->get("font_size") + 12;
		}

		// Udskriver overskrift

		$pointX = $this->get("margin_left");
		CorePdf::addText($pointX, $pointY, $this->get("font_size") + 8, $title);

		return($pointY);
	}
	*/

	/**
	 *
	 * @param parameter: array("customer" => (object), "payment_text" => (string), "amount" => (double), "due_date" => (string), "girocode" => (string));
	 */

	/*
	Hører ikke til her
	function addPaymentCondition($pointY, $payment_method, $parameter) {
		if(!is_array($parameter)) {
			trigger_error("den 3. parameter til addPaymentCondition skal være et array!", FATAL);
		}

		// Indbetalingsoplysninger

		if($payment_method > 0) {
			$pointY -= 20; // Afstand ned til betalingsinfo
			$pointX = $this->get("margin_left");

			$payment_line = 26;
			$payment_left = 230;
			$payment_right = $this->get("page_width") - $this->get("margin_left") - $payment_left;

			if($pointY < $this->get("margin_bottom") + $this->get("font_spacing") + 4 + $payment_line * 3) {
				$pointY = $this->nextPage();
			}

			// Sort bjælke
			$this->setLineStyle(1);
			$this->setColor(0, 0, 0);
			$this->filledRectangle($this->get("margin_left"), $pointY - $this->get("font_spacing") - 4, $this->get("page_width") - $this->get("margin_left"), $this->get("font_spacing") + 4);
			$this->setColor(1, 1, 1);
			$pointY -= $this->get("font_size") + $this->get("font_padding_top") + 2;
			$this->addText($pointX + 4, $pointY, $this->get("font_size") + 2, "Indbetalingsoplysninger");
			$this->setColor(0, 0, 0);
			$pointY -= $this->get("font_padding_bottom") + 2;

			$payment_start = $pointY;

			if($payment_method == 1) {

				$this->rectangle($pointX, $pointY - $payment_line * 2, $this->get("page_width") - $this->get("margin_left"), $payment_line * 2);
				$this->line($pointX + $payment_left, $pointY - $payment_line * 2, $pointX + $payment_left, $pointY);
				$this->line($pointX, $pointY - $payment_line, $this->get("page_width"), $pointY - $payment_line);
				$this->line($pointX + $payment_left / 2, $pointY - $payment_line * 2, $pointX + $payment_left / 2, $pointY - $payment_line);

				$pointY -= 7;
				$this->addText($pointX + 4, $pointY, $this->get("font_size") - 4, "Bank:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + 10, $pointY, $this->get("font_size"), $this->kernel->setting->get("intranet", "bank_name"));

				$pointY = $payment_start;
				$pointY -= 7;

				$this->addText($pointX + $payment_left + 4, $pointY, $this->get("font_size") - 4, "Tekst til modtager:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + 10, $pointY, $this->get("font_size"), $parameter["payment_text"]);

				$pointY = $payment_start - $payment_line;
				$pointY -= 7;

				$this->addText($pointX + 4, $pointY, $this->get("font_size") - 4, "Beløb DKK:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + 10, $pointY, $this->get("font_size"), number_format($parameter["amount"], 2, ",", "."));

				$pointY = $payment_start - $payment_line;
				$pointY -= 7;

				$this->addText($pointX + $payment_left / 2 + 4, $pointY, $this->get("font_size") - 4, "Betalingsdato:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left / 2 + 10, $pointY, $this->get("font_size"), $parameter["due_date"]);

				$pointY = $payment_start - $payment_line;
				$pointY -= 7;


				$this->addText($pointX + $payment_left + 4, $pointY, $this->get("font_size") - 4, "Regnr.:            Kontonr.:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + 10, $pointY, $this->get("font_size"), $this->kernel->setting->get("intranet", "bank_reg_number")."       ".$this->kernel->setting->get("intranet", "bank_account_number"));

			}
			elseif($payment_method == 2) {

				$this->rectangle($pointX, $pointY - $payment_line * 3, $this->get("page_width") - $this->get("margin_left"), $payment_line * 3);
				$this->line($pointX + $payment_left, $pointY - $payment_line * 3, $pointX + $payment_left, $pointY);
				$this->line($pointX + $payment_left, $pointY - $payment_line, $this->get("page_width"), $pointY - $payment_line);
				$this->line($pointX + $payment_left, $pointY - $payment_line * 2, $this->get("page_width"), $pointY - $payment_line * 2);
				$this->line($pointX + $payment_left + $payment_right / 2, $pointY - $payment_line * 2, $pointX + $payment_left + $payment_right / 2, $pointY - $payment_line);

				$pointY -= 7;
				$this->addText($pointX + 4, $pointY, $this->get("font_size") - 4, "Indbetaler:");
				$pointY -= $this->get("font_spacing");
				$this->addText($pointX + 10, $pointY, $this->get("font_size"), $parameter["customer"]->address->get("name"));
				$pointY -= $this->get("font_spacing");
				$line = explode("\r\n", $parameter["customer"]->address->get("address"));
				for($i = 0; $i < count($line); $i++) {
					$this->addText($pointX + 10, $pointY, $this->get("font_size"), $line[$i]);
					$pointY -= $this->get("font_spacing");
					if($i == 2) $i = count($line);
				}
				$this->addText($pointX + 10, $pointY, $this->get("font_size"), $parameter["customer"]->address->get("postcode")." ".$parameter["customer"]->address->get("city"));

				$pointY = $payment_start;
				$pointY -= 7;

				$this->addText($pointX + $payment_left + 4, $pointY, $this->get("font_size") - 4, "Tekst til modtager:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + 10, $pointY, $this->get("font_size"), $parameter["payment_text"]);

				$pointY = $payment_start - $payment_line;
				$pointY -= 7;

				$this->addText($pointX + $payment_left + 4, $pointY, $this->get("font_size") - 4, "Beløb DKK:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + 10, $pointY, $this->get("font_size"), number_format($parameter["amount"], 2, ",", "."));

				$pointY = $payment_start - $payment_line;
				$pointY -= 7;

				$this->addText($pointX + $payment_left + $payment_right / 2 + 4, $pointY, $this->get("font_size") - 4, "Betalingsdato:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + $payment_right / 2 + 10, $pointY, $this->get("font_size"), $parameter["due_date"]);

				$pointY = $payment_start - $payment_line * 2;
				$pointY -= 7;


				$this->addText($pointX + $payment_left + 4, $pointY, $this->get("font_size") - 4, "Kodelinje: (Ej til maskinel aflæsning)");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + 10, $pointY, $this->get("font_size"), "+01<".str_repeat(" ", 20)."+".$this->kernel->setting->get("intranet", "giro_account_number")."<");
			}
			elseif($payment_method == 3) {

				$this->rectangle($pointX, $pointY - $payment_line * 2, $this->get("page_width") - $this->get("margin_left"), $payment_line * 2);
				$this->line($this->get("margin_left"), $pointY - $payment_line, $this->get("page_width"), $pointY - $payment_line);
				$this->line($pointX + $payment_left, $pointY, $pointX + $payment_left, $pointY - $payment_line);

				$pointY -= 7;

				$this->addText($pointX + 4, $pointY, $this->get("font_size") - 4, "Beløb DKK:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + 10, $pointY, $this->get("font_size"), number_format($parameter["amount"], 2, ",", "."));

				$pointY = $payment_start;
				$pointY -= 7;

				$this->addText($pointX + $payment_left + 4, $pointY, $this->get("font_size") - 4, "Betalingsdato:");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + $payment_left + 10, $pointY, $this->get("font_size"), $parameter["due_date"]);

				$pointY = $payment_start - $payment_line;
				$pointY -= 7;

				$this->addText($pointX + 4, $pointY, $this->get("font_size") - 4, "Kodelinje: (Ej til maskinel aflæsning)");
				$pointY -= $payment_line - 12;
				$this->addText($pointX + 10, $pointY, $this->get("font_size"), "+71< ".str_repeat("0", 15 - strlen($parameter["girocode"])).$parameter["girocode"]." +".$this->kernel->setting->get("intranet", "giro_account_number")."<");

			}
		}
		return($pointY);
	}
	*/
}
?>