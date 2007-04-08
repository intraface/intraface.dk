<?php
/**
 * CMS_HTML_Parser
 *
 * Parses content of the array returned from the Intraface CMS XML-RPC
 * to valid XHTML 1.0.
 *
 * Example:
 * -------
 *
 * // get array from your XML-RPC-client
 * $page_array = $client->getPage();
 *
 * // put array into the parser
 * $html = new CMS_HTML_Parser($page_array);
 * $head = $html->parseMeta();
 * $navigation = $html->parseNavigation('toplevel');
 * $content = $html->parseContent();
 *
 * If you are not satisfied with the returned result from the class, you
 * can make your own parser-functions by extending this class with your own custom
 * methods:
 *
 * Example:
 * -------
 *
 * class MyHTMLParser extends CMS_HTML_Parser {
 *		function parseHtmltextElement($element) {
 *			return '<div class="my-own-class">' . $element['html'] . '</div>;
 *		}
 * }
 *
 * Never rewrite the main class, as it would be much harder to upgrade to a new
 * one, when we make new functions in the cms system.
 *
 * @package Intraface CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   1.0
 * @version	1.1
 *
 * This software is released under Creative Commons / Share A Like license (by-sa):
 * http://creativecommons.org/licenses/by-sa/2.5/
 * http://creativecommons.org/licenses/by-sa/2.5/legalcode
 */
class CMS_HTML_Parser {

	/**
	 * Constructor
	 *
	 * @param  array  $page_array  Array with information about a page
	 */
	function CMS_HTML_Parser(& $page_array) {
		CMS_HTML_Parser::__construct($page_array);
	}

	function __construct(& $page_array) {
		if (!is_array($page_array)) {
			trigger_error('CMS_HTML_Parser::__construct: $page_array is not an array', E_USER_ERROR);
		}
		$this->page_array = & $page_array;
	}

	/****************************************************************************
	 * Headers
	 ****************************************************************************/

	/**
	 * Sets a couple of headers.
	 * Notice: Make sure that this is used before outputting any data
	 */
	function httpHeaders() {
		header($this->page_array('http_header_status'));
		// the encoding should also be set
	}


	/****************************************************************************
	 * HTML-outline
	 ****************************************************************************/

	function parsePage() {
		$output  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$output .= '<html xml:lang="'.$this->escape($this->page_array['language']).'" xmlns="http://www.w3.org/1999/xhtml">';
		$output .= '	<head>';
		$output .= 			$this->parseMeta();
		$output .= '		<style type="text/css">';
		$output .=				$this->page_array['css'];
		$output .= '		</style>';
		$output .= '	</head>';
		$output .= '	<body>';
		$output .= '		<div id="container">';
		$output .= '			<div id="branding">';
		$output .= '				<h1>'.$this->escape($this->page_array['title']).'</h1>';
		$output .= '			</div>';
		$output .= 				$this->parseNavigation('toplevel');
		//$output .= 				$this->parseNavigation('sublevel');
		$output .= '			<div id="content">';
		$output .= '				<div id="content-main">';
		$output .= 						$this->parseSections();
		$output .= '				</div>';
		$output .= '			</div>';
		$output .= '			<div id="siteinfo">';
		// her kunne vi outputte licensen
		$output .= '			</div>';
		$output .= '		</div>';
		$output .= '	</body>';
		$output .= '</html>';

		return $output;

	}


	/****************************************************************************
	 * Metadata
	 ****************************************************************************/

	/**
	 * This method returns the following tags to put in the header section of
	 * a page
	 * - title
	 * - metatags
	 * - encoding
	 * - language
	 *
	 * @return	string
	 */
	function parseMeta() {
		$output  = '<title>'.$this->page_array['title'].'</title>';
		$output .= '<meta http-equiv="content-type" content="'.$this->escape($this->page_array['content_type']).'" />';
		$output .= '<meta name="description" content="'.$this->escape($this->page_array['description']).'" />';
		$output .= '<meta name="keywords" content="'.$this->escape($this->page_array['keywords']).'" />';
		return $output;
	}

	/****************************************************************************
	 * Navigation
	 ****************************************************************************/

	/**
	 * This method returns the navigation as an unordered list
	 *
	 * @return	string
	 */
	function parseNavigation($level = 'toplevel') {
		$first = true;
		$pages = $this->page_array['navigation_'.$level];
		$output  = '<ul id="navigation-'.$level.'">';
		if (!is_array($pages) OR count($pages) == 0) {
			return '';
		}
		foreach ($pages AS $page) {
			$output .= '<li';
			$id = '';
			//if ($this->page_array['id'] == $page['id']) {
			if ($page['current'] == 'yes') {
				$output .= ' id="navigation-current"';
			}
			if ($first) {
				$output .= ' class="navigation-first-item"';
			}

			$output .= '><a href="'.$page['url'] .'">'.$this->escape($page['navigation_name']).'</a>';
			$output .= '</li>';
			$first = false;
		}
		$output .= '</ul>';

		return $output;
	}

	/****************************************************************************
	 * Pagelist
	 ****************************************************************************/

	 /**
	  * This method will write pagelist
	  */


	/****************************************************************************
	 * Sections
	 ****************************************************************************/


	function getSection($identifier) {
		if (!is_array($this->page_array['sections'])) {
			return '';
		}
		foreach ($this->page_array['sections'] AS $section) {
			if ($section['section_identifier'] == $identifier) {
				$this_section = $section;
			}
		}
		if ($this_section['type'] == 'mixed') {
			$this_section['html'] = $this->parseElements($this_section['elements']);
		}

		return $this_section;
	}


	/**
	 * This method parses the sections.
	 *
	 *
	 */

	function parseSections() {
		if (!isset($this->page_array['sections']) OR !is_array($this->page_array['sections']) OR count($this->page_array['sections']) == 0) {
			return 0;
		}

		$output = '';

		foreach ($this->page_array['sections'] AS $section) {
			$function = 'parse' . $section['type'] . 'Section';
			$output .= $this->$function($section);
		}

		return $output;
	}

	function parseShortTextSection($section) {
		return '<h2>' . $section['text'] . '</h2>';
	}

	function parseLongTextSection($section) {
		return $section['html'];
	}

	function parsePictureSection($section) {
		return '<img src="'.$section['picture']['file_uri'].'" alt="'.$this->escape($section['pic_text']).'" width="'.intval($section['picture']['width']).'" height="'.intval($section['picture']['height']).'" />';
	}

	function parseMixedSection($section) {
		return $this->parseElements($section['elements']);
	}


	/****************************************************************************
	 * Elements
	 ****************************************************************************/

	function parseElements($elements) {
		if (!is_array($elements) OR count($elements) == 0) {
			return '';
		}

		$output = '';
		foreach ($elements AS $element) {
			$extra_class = '';
			$extra_style = '';

			if (!empty($element['extra_class'])) {
				$extra_class = ' class="'.$element['extra_class'].'"';
			}
			if (!empty($element['extra_style'])) {
				$extra_style = ' style="'.$element['extra_style'].'"';
			}

			$function = 'parse' .  $element['type'] . 'Element';
			$output .= '<div'.$extra_class.$extra_style.'>';
			$output .= $this->$function($element);
			$output .= '</div>';
		}
		return $output;
	}

	function parseDeliciousElement($element) {

		if (empty($element)) {
			return '';
		}

		$links = '<ul class="cms-delicious">';
		foreach($element['items'] AS $item) {

			$links .=  '<li><a href="' . $this->parseUrl($item['link']) . '" title="' . htmlentities($item['description']). '">' . htmlentities($item['title']) . '</a></li>';
		}
		$links .= '</ul>';

		return $links;

	 }

	 /**
	  * Flickr-viewer
	  *
	  * You got different possibilities for showing your Flickr-pictures. We recommend
	  * that you just return the $element['pictobrowser'], which is a great free
	  * little piece of software, which will show your pictures:
	  *
	  * Example:
	  * -------
	  * return $element['pictobrowser'];
	  *
	  * If you wish to show your pictures on your own page using Flickr's own
	  * slideshow possibilites, here is some tips:
	  * http://www.lifehacker.com/software/flickr/how-to-embed-flickr-slideshows-210683.php
	  */

	 function parseFlickrElement($element) {

	 	if (!empty($element['pictobrowser'])) {
			return $element['pictobrowser'];
		}

		return '
			<p style="background: #eee; border: 2px solid #ccc; padding: 1em;">
				<a href="' . $element['set']['url'] . '">'.$element['set']['info']['title'].'</a>
			</p>';

			/*
			if (!is_array($photos) AND count($photos) == 0) {
				$output = '<p>Ingen photos på Flickr-søgningen</p>';
			}
			else {
				$ouput = '<div class="flickr-photos">';
				foreach ($photos as $photo) {
					//echo $photo;
					$owner = $f->people_getInfo($photo['owner']);
					$output .= '<div class="flickr-photo">';
					$output .= '	<a href="' . $photos_url . $photo['id'] . '/">';
					$output .= '		<img src="'.$f->buildPhotoURL($photo, $this->parameter->get('size')).'" alt="'.$photo['title'].'" />';
					$output .= '	</a>';
					//$output .= '&copy; <a href="http://www.flickr.com/people/' . $photo['owner'] . '/">';
					//$output .= $owner['username'];
					//$output .= "</a>';
					$output .= '</div>';
				}
				$ouput .= '</div>';
			}
		}
		else {
			$output = '<p>Flickrkoden er forkert</p>';
		}

		return $output;
		*/
	 }

	 /**
	  * Det kan nok være en god ide, at man på sigt selv får lov at bestemme størrelserne
	  */


	 function parseGalleryElement($element) {
	 	$output  = '<div class="cms-gallery">';

		foreach ($element['pictures'] AS $file) {
			$output .= '<div class="cms-gallery-item">';
			$output .= '	<a href="'.$file['instances'][4]['file_uri'] .'">';
			$output .= '		<img src="'.$file['instances'][3]['file_uri'].'" alt="" />';
			$output .= '	</a>';
			$output .= '</div>';

		}

		$output .= '</div>';

		return $output;

	 }

	function parseHtmltextElement($element) {
		return $element['html'];
	}

	/**
	 * This function supports permalinks which is the following addition to a link:
	 * <a rel="bookmark" ...>Tekst</a>
	 */

	function parsePageListElement($element) {

		$output  = '<div class="pagelist">';
		if ($element['headline']) {
			$output .= '<h2>'.$element['headline'].'</h2>';
		}

		if (!is_array($element['pages']) OR count($element['pages']) == 0) {
			$output .= '<p>Listen er tom.</p>';
			$output .= '</div>';
			return $output;
		}


		$output .= '<dl class="pagelist">';


		foreach ($element['pages'] AS $page) {
			$output .= '<dt><a rel="bookmark" href="'.$page['url'] .'">'.$page['title'].'</a></dt>';
			if ($element['show'] == 'description') {
				$output .= '<dd>'.$page['description'].' <a href="'.$page['url'].'">Læs mere</a>.</dd>';
				// tilføj beskrivelsen til de enkelte sider
			}

		}
		// finde alle siderne
		// tag højde for keywords, lifetime, type, og hvor meget der skal vises fra siden


		$output .= '<dl>';
		$output .= '</div>';
		return $output;

	 }

	/**
	 *
	 */

	function parsePictureElement($element) {

		$output = '';
		if (empty($element['picture'])) return '';
		if (empty($element['picture']['width']) OR empty($element['picture']['height'])) return '<img src="'.$element['picture']['file_uri'].'" alt="" />';

		if (!empty($element['pic_url'])) {
			$output .= '<a href="'.$element['pic_url'].'">';

		}

		$output .= '<img width="'.$element['picture']['width'].'" height="'.$element['picture']['height'].'" src="' . $element['picture']['file_uri'] . '" alt="'.$element['pic_text'].'" />';

		if (!empty($element['pic_url'])) $output .= '</a>';

		return $output;
	 }

	 function parseMapElement($element) {


		return $element['map'];
	 }

	/**
	 * Skal denne indeholde selve playeren, så man selv let kan lave det om?
	 *
	 */
	function parseVideoElement($element) {
		return $element['player'];
	}

	/**
	 * Make sure that the descriptions are translated
	 *
	 */


	function parseFileListElement($element) {
	 	if (!is_array($element['files']) OR count($element['files']) == 0) {
			return '';
		}
		$output  = '<table summary="" class="filelist-table">';
		if (!empty($element['caption'])) {
			$output .= '	<caption>'.$element['caption'].'</caption>';
		}
		$output .= '	<colgroup>';
		$output .= '		<col class="filename"></col>';
		$output .= '		<col class="filedescription"></col>';
		$output .= '		<col class="filetype"></col>';
		$output .= '		<col class="filesize"></col>';
		$output .= '	</colgroup>';
		$output .= '	<tr>';
		$output .= '		<th scope="col">Filbeskrivelse</th>';
		$output .= '		<th scope="col">Filnavn</th>';
		$output .= '		<th scope="col">Filtype</th>';
		$output .= '		<th scope="col">Filstørrelse</th>';
		$output .= '	</tr>';

 		foreach ($element['files'] AS $file) {
			$output .= '<tr>';
			$output .= '	<td>'.$file['description'].'</td>';
			$output .= '	<td><a href="'.$file['file_uri'].'">'.$file['file_name'].'</a></td>';
			$output .= '	<td>' . $file['file_type']['mime_type'] . '</td>';
			$output .= '	<td>' . $file['dk_file_size'] . '</td>';
			$output .= '</tr>';
		}

		$output .= '</table>';
	 	return $output;
	 }

	 function escape($value) {
		return htmlspecialchars($value);

	 }

	 function parseUrl($url) {
		$url = parse_url($url);
		$sanitized_url = $url['scheme'] . '://' . $url['hostname'] . $url['path'];
		return $sanitized_url;
	 }

	 function autoop() {
	 }
}
?>