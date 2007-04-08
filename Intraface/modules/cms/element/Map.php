<?php
/**
 * This element demands that we are not operating in xml mode
 *
 * neccessary javascript http://api.maps.yahoo.com/v2.0/fl/javascript/apiloader.js
 *
 */

require_once('HTTP/Request.php');
require_once('XML/Unserializer.php');

class CMS_Map extends CMS_Element {

	var $services = array('yahoo');

	function __construct(& $section, $id = 0) {
		# hm, det her skal vi lave, så man lige får instantieret element,
		# men hvornår skal det ske.
		$this->value['type'] = 'map';
		parent::__construct($section, $id);

	}

	function yahoo_geo($location) {

		$q = 'http://api.local.yahoo.com/MapsService/V1/geocode';
		$q .= '?appid=intraface&location='.rawurlencode(utf8_encode($location));

		$req =& new HTTP_Request($q);
		if (PEAR::isError($req->sendRequest())) {
			echo 'error';
			return array();
		}
		$xml = $req->getResponseBody();


		// create object
		$unserializer = &new XML_Unserializer();

		// unserialize the document
		$result = $unserializer->unserialize($xml, false);

		// dump the result
		$data = $unserializer->getUnserializedData();

		//print_r($data);

		return $data;
	}

	function load_element() {
		$this->value['service'] = $this->parameter->get('service');
		$this->value['address'] = $this->parameter->get('address');

		$this->value['map'] = '';

		if ($this->value['service'] == 'yahoo') {
			$a = $this->yahoo_geo($this->value['address']);
			//print_r($a);
			if (!empty($a['Result']['Longitude']) AND !empty($a['Result']['Latitude'])) {
				$this->value['map']  = '<script type="text/javascript" src="http://api.maps.yahoo.com/ajaxymap?'.htmlentities('v=2.0&appid=intraface') .'"></script>';
				/* flash version
				$this->value['map'] .= '<script type="text/javascript">';
				$this->value['map'] .= '	var latlon = new LatLon(' .$a['ResultSet']['Result']['Latitude'] . ', '. $a['ResultSet']['Result']['Longitude'].');';
				$this->value['map'] .= '	var map = new Map("mapContainer", "rlerdorf", latlon, 3);';
				$this->value['map'] .= '	map.addTool( new PanTool(), true );';
				$this->value['map'] .= '</script>';
				*/
				$this->value['map'] .= '<div id="mapContainer" style="width: 600px; height: 600px;"></div>';

				$this->value['map'] .= '<script type="text/javascript">';
				//$this->value['map'] .= '<![CDATA[';
				$this->value['map'] .= '	function onSmartWinEvent() {';
  				$this->value['map'] .= '		var words = "title";';
  				$this->value['map'] .= '		marker.openSmartWindow(words);';
				$this->value['map'] .= '	} ';
				$this->value['map'] .= '	var latlon = new YGeoPoint(' .$a['Result']['Latitude'] . ', '. $a['Result']['Longitude'].');';
				$this->value['map'] .= '	var mymap = new  YMap(document.getElementById("mapContainer"));';
				$this->value['map'] .= '	var marker = new YMarker(latlon);';
				$this->value['map'] .= '	marker.addLabel("<b>A</b>"); ';
				$this->value['map'] .= '	YEvent.Capture(marker, EventsList.MouseClick, onSmartWinEvent);';
				$this->value['map'] .= '	mymap.addOverlay(marker);';
				$this->value['map'] .= '	mymap.addPanControl();';
				$this->value['map'] .= '	mymap.addZoomLong();';
				$this->value['map'] .= '	mymap.drawZoomAndCenter(latlon, 3);';
				//$this->value['map'] .= ']]>';
				$this->value['map'] .= '</script>';
			}
		}

	}

	function validate_element($var) {

		if (!in_array($var['service'], $this->services)) {
			$this->error->set('error in service - unknown');
		}

		$validator = new Validator($this->error);
		$validator->isString($var['address'], 'error in address');
		$validator->isString($var['service'], 'error in service');

		if ($this->error->isError()) {
			return 0;
		}

		return 1;
	}


	function save_element($var) {
		if (!empty($var['service'])) $this->parameter->save('service', $var['service']);
		if (!empty($var['address'])) $this->parameter->save('address', $var['address']);

		return 1;
	}

}

?>