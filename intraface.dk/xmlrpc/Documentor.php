<?php
require_once('3Party/IXR/IXR.php');

class XMLRPC_Documentor {

	var $client;
	var $server_uri;
	var $description;

	function XMLRPC_Documentor($server_uri) {
		XMLRPC_Documentor::__construct($server_uri);
	}

	function setDescription($description) {
		$this->description = $description;
	}

	function __construct($server_uri) {
		$this->server_uri = $server_uri;
		$this->client= new IXR_Client($this->server_uri);
	}

	function prepare() {
		if (!$this->client->query('system.listMethods')) {
			trigger_error($this->client->getErrorCode(). ' : '.$this->client->getErrorMessage());
			return false;

		}
		$methods = $this->client->getResponse();
		$i = 0;
		$method = array();
		foreach ($methods AS $m) {
			$method[$i]['name'] = $m;
			$this->client->query('system.methodSignature', $m);
			$method[$i]['signature'] = $this->client->getResponse();
			$this->client->query('system.methodHelp', $m);
			$method[$i]['help'] = $this->client->getResponse();
			$i++;
		}
		return $method;
	}

	function display() {
		$methods = $this->prepare();

		$output  = '<html>';
		$output .= '	<head>';
		$output .= '		<title>' .$this->server_uri. '</title>';
		$output .= '	</head>';
		$output .= '	<style type="text/css">';
 		$output .= '		caption { border-bottom: 1px solid black; }';
 		$output .= '		th { text-align: left; }';
		$output .= '	</style>';
		$output .= '	<body>';

		$output .= '<h1>Documentation</h1>';
		$output .= '<h2>Server info</h2>';
		$output .= '<p><strong>Address</strong>: '.$this->server_uri.'</p>';
		$output .= '<div>' . $this->description.'</div>';

		$output .= '<h2>Methods</h2>';

		if (count($methods) > 0) {

			$output .= '<table>';
			$output .= '	<caption>Methods</caption>';
			$output .= '	<thead>';
			$output .= '	<tr>';
 			$output .= '		<th>Name</th>';
 			$output .= '		<!--<th>Returns</th>';
 			$output .= '		<th>Signature</th>-->';
 			$output .= '		<th>Help</th>';

			$output .= '	</tr>';
			$output .= '	</thead>';


			// Fremstiller en liste af koloner for at kunne sortere
			foreach ($methods as $key => $row) {
				$name[$key]  = $row['name'];
			}

			array_multisort($name, SORT_ASC, $methods);

			$output .= '	<tbody>';
			foreach ($methods AS $method) {
				$output .= '<tr>';
				$output .= '	<td>' . $method['name'] . '</td>';
				/*
				$output .= '	<td>' . $method['signature'][0] . '</td>';
				$output .= '	<td>';
				for ($i = 1, $max = count($method['signature']); $i < $max; $i++) {
					$output .= $method['signature'][$i];
				}
				$output .= '	</td>';
				*/
				//$output .= '	<td>'.implode(', '$method['signature']).'</td>';
				$output .= '	<!--<td>Will be supported</td>';
				$output .= '	<td>Will be supported</td>-->';
				$output .= '	<td>' . $method['help'] . '</td>';
				$output .= '</tr>';

			}
			$output .= '</tbody>';
			$output .= '</table>';


		}
		else {
			$output .= '<p>There are no methods on this server.</p>';
		}

		$output .= '<h2>Structs</h2>';
		$output .= '<h3><code>struct</code> <var>$credentials</var></h3>';
		$output .= '<table>';
		$output .= '<thead>';
		$output .= '<tr>';
		$output .= '	<th>Type</th>';
		$output .= '	<th>Member name</th>';
		$output .= '	</tr>';
		$output .= '	</thead>';
		$output .= '	<tbody>';
		$output .= '	<tr>';
		$output .= '	<td><em>string</em></td>';
		$output .= '	<td>private_key</td>';
		$output .= '	</tr>';
		$output .= '	<tr>';
		$output .= '	<td><em>string</em></td>';
		$output .= '	<td>session_id</td>';
		$output .= '	</tr>';

		$output .= '	</tbody>';
		$output .= '	</table>';


		$output .= '<h2>Error handling</h2>';
		$output .= '<dl>';
		$output .= '<dt>1. Unknown method</dt>';
		$output .= '<dt>2. Access denied</dt>';
		$output .= '<dt>3. Not implemented</dt>';
		$output .= '<dt>4. Wrong argument count</dt>';
		$output .= '<dt>5. Wrong input</dt>';
		$output .= '<dt>6. Method call failed - something wrong with input</dt>';
		$output .= '</dl>';


		$output .= '	</body>';
		$output .= '</html>';

		return $output;

	}

}

?>