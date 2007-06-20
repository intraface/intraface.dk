<?php
/**
 * This element demands that we are not operating in xml mode
 *
 * neccessary javascript http://api.maps.yahoo.com/v2.0/fl/javascript/apiloader.js
 *
 */

require_once 'HTTP/Request.php';
require_once 'XML/Unserializer.php';
require_once 'Intraface/modules/cms/Element.php';

class CMS_Map extends CMS_Element {

    var $services = array('yahoo', 'google');

    function __construct($section, $id = 0) {
        $this->value['type'] = 'map';
        parent::__construct($section, $id);
    }

    function load_element() {
        $this->value['service'] = $this->parameter->get('service');
        $this->value['text'] = $this->parameter->get('text');
        $this->value['latitude'] = $this->parameter->get('latitude');
        $this->value['longitude'] = $this->parameter->get('longitude');
        $this->value['width'] = $this->parameter->get('width');
        $this->value['height'] = $this->parameter->get('height');
        $this->value['api_key'] = $this->parameter->get('api_key');

        $this->value['map'] = '';

        if ($this->value['service'] == 'yahoo') {
            if (is_object($this->section->kernel->user)) {
                $api = 'intraface';
            }
            else {
                $api = $this->get('api_key');
            }

                $this->value['map']  = '<script type="text/javascript" src="http://api.maps.yahoo.com/ajaxymap?'.htmlentities('v=2.0&appid=' . $api) .'"></script>';
                /* flash version
                $this->value['map'] .= '<script type="text/javascript">';
                $this->value['map'] .= '	var latlon = new LatLon(' .$a['ResultSet']['Result']['Latitude'] . ', '. $a['ResultSet']['Result']['Longitude'].');';
                $this->value['map'] .= '	var map = new Map("mapContainer", "rlerdorf", latlon, 3);';
                $this->value['map'] .= '	map.addTool( new PanTool(), true );';
                $this->value['map'] .= '</script>';
                */
                $this->value['map'] .= '<div id="mapContainer" style="width: '.$this->get('width').'px; height: '.$this->get('height').'px;"></div>';

                $this->value['map'] .= '<script type="text/javascript">';
                //$this->value['map'] .= '<![CDATA[';
                $this->value['map'] .= '	function onSmartWinEvent() {';
                $this->value['map'] .= '		var words = "title";';
                $this->value['map'] .= '		marker.openSmartWindow(words);';
                $this->value['map'] .= '	} ';
                $this->value['map'] .= '	var latlon = new YGeoPoint(' .$this->get('latitude') . ', '. $this->get('longitude').');';
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
        } elseif ($this->value['service'] == 'google') {
            if (is_object($this->section->kernel->user)) {
                $api = 'ABQIAAAAUFgD-PSpsw5MDGYzf-NyqBT5Xij7PtUjdkWMhSxoVKuMOjPcWxR5Rf13LT-bMD4Iiu_tpJ5XdRMJ3g';
            }
            else {
                $api = $this->get('api_key');
            }

                $this->value['map']  = '<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$api.'"></script>';
                $this->value['map'] .= '<div id="mapContainer" style="width: '.$this->get('width').'px; height: '.$this->get('height').'px;"></div>';

                $this->value['map'] .= '<script type="text/javascript">';
                //$this->value['map'] .= '<![CDATA[';
                $this->value['map'] .= 'function load() {';
                $this->value['map'] .= '    if (GBrowserIsCompatible()) {';
                $this->value['map'] .= '        var map = new GMap2(document.getElementById("mapContainer"));';
                $this->value['map'] .= '        map.setCenter(new GLatLng('.$this->get('latitude').', '.$this->get('longitude').'), 13);';
                $this->value['map'] .= '        var point = new GLatLng('.$this->get('latitude').', '.$this->get('longitude').');';
                $this->value['map'] .= '        map.addOverlay(new GMarker(point));';
                $this->value['map'] .= '    }';
                $this->value['map'] .= '}';
                $this->value['map'] .= 'load();';
                $this->value['map'] .= '    var latlon = new YGeoPoint(' .$this->get('latitude') . ', '. $this->get('longitude').');';
                $this->value['map'] .= '    var mymap = new  YMap(document.getElementById("mapContainer"));';
                $this->value['map'] .= '    var marker = new YMarker(latlon);';
                $this->value['map'] .= '    marker.addLabel("<b>A</b>"); ';
                $this->value['map'] .= '    YEvent.Capture(marker, EventsList.MouseClick, onSmartWinEvent);';
                $this->value['map'] .= '    mymap.addOverlay(marker);';
                $this->value['map'] .= '    mymap.addPanControl();';
                $this->value['map'] .= '    mymap.addZoomLong();';
                $this->value['map'] .= '    mymap.drawZoomAndCenter(latlon, 3);';
                //$this->value['map'] .= ']]>';
                $this->value['map'] .= '</script>';
        }

    }

    function validate_element($var) {

        if (!in_array($var['service'], $this->services)) {
            $this->error->set('error in service - unknown');
        }

        $validator = new Validator($this->error);
        $validator->isString($var['text'], 'error in text');
        $validator->isString($var['service'], 'error in service');
        $validator->isString($var['api_key'], 'error in api key');
        $validator->isNumeric($var['width'], 'error in width');
        $validator->isNumeric($var['height'], 'error in height');
        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    function save_element($var) {
        if (!empty($var['service'])) $this->parameter->save('service', $var['service']);
        if (!empty($var['text'])) $this->parameter->save('text', $var['text']);
        if (!empty($var['latitude'])) $this->parameter->save('latitude', $var['latitude']);
        if (!empty($var['longitude'])) $this->parameter->save('longitude', $var['longitude']);
        if (!empty($var['height'])) $this->parameter->save('height', $var['height']);
        if (!empty($var['width'])) $this->parameter->save('width', $var['width']);
        if (!empty($var['api_key'])) $this->parameter->save('api_key', $var['api_key']);
        return true;
    }

}
?>