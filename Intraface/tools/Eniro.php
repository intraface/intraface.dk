<?php
require_once('HTTP/Request.php');
require_once('XML/Unserializer.php');

## vihs oplysninger
define('ENIRO_PINCODE', 1108);
define('ENIRO_INDTYPE', 3);

class Eniro {

	private $pincode;
	private $indtype;
	private $status = 'online';

	function Eniro($pincode, $indtype) {
		$this->pincode = $pincode;
		$this->indtype = $indtype;
	}

	function find($field, $query) {
		$link = 'http://www.eniro.dk/ctk/ctk.xQuery?p_pinkode='.$this->pincode.'&p_indtype='.$this->indtype.'&p_'.$field.'='.$query;
		$req = & new HTTP_Request(
			$link,
			array(
				'timeout', 3
			)
		);

		if (PEAR::isError($req->sendRequest())) {
			return 0;
		}

		$xml = $req->getResponseBody();

		// create object
		$unserializer = &new XML_Unserializer();

		// unserialize the document
		$result = $unserializer->unserialize($xml, false);

		// dump the result
		$data = $unserializer->getUnserializedData();

		if (PEAR::isError($data)) {
			return array(
				'navn' => 'Ingen data fundet - pga. en fejl',
				'adresse' => '',
				'postnr' => '',
				'postby' => ''
			);
		}

		if (empty($data['SVARLISTE'])) {
			return array(
				'navn' => 'Ingen data fundet',
				'adresse' => '',
				'postnr' => '',
				'postby' => ''
			);
		}
		if (empty($data['SVARLISTE']['RAEKKE'][0])) {
			return array(
				'navn' => $data['SVARLISTE']['RAEKKE']['NAVN'],
				'adresse' => $data['SVARLISTE']['RAEKKE']['ADRESSE'],
				'postnr' => $data['SVARLISTE']['RAEKKE']['POSTNR'],
				'postby' => $data['SVARLISTE']['RAEKKE']['BYNAVN'],
				'email' => $data['SVARLISTE']['RAEKKE']['EMAIL'],
				'website' => $data['SVARLISTE']['RAEKKE']['WEB']
			);
		}
		else {
			return array(
				'navn' => $data['SVARLISTE']['RAEKKE'][0]['NAVN'],
				'adresse' => $data['SVARLISTE']['RAEKKE'][0]['ADRESSE'],
				'postnr' => $data['SVARLISTE']['RAEKKE'][0]['POSTNR'],
				'postby' => $data['SVARLISTE']['RAEKKE'][0]['BYNAVN'],
				'email' => $data['SVARLISTE']['RAEKKE'][0]['EMAIL'],
				'website' => $data['SVARLISTE']['RAEKKE'][0]['WEB']
			);

		}
	}
}
?>