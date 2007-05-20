<?php
require_once 'IntrafacePublic/CMS/HTML/Parser.php';

class CMS_Admin_HTML_Parser extends IntrafacePublic_CMS_HTML_Parser {

    function __construct() {
    }

    function parseElements($elements) {
        $display = '';
        if (empty($elements)) {
            return $display;
        }

        foreach ($elements AS $element) {
            $element['extra_class'] .= ' element';
            if (!empty($element['extra_class'])) {
                $element['extra_class'] = ' class="'.$element['extra_class'].'"';
            }
            if (!empty($element['extra_style'])) {
                $element['extra_style'] = ' style="'.$element['extra_style'].'"';
            }

            $function =  'parse'.$element['type'] . 'Element';
            $output = $this->$function($element);

            $display .= '<div id="element-'.$element['id'].'"'.$element['extra_class'].$element['extra_style'].'>';
            $display .= '	<ul class="adminbar" id="admin'.$element['id'].'">';
            $display .= '		<li><a href="section_html_edit.php?id='.$element['id'].'" title="Rediger elementet. Skifter side.">Rediger</a></li>';

            if (!empty($_GET['action']) AND $_GET['action'] == 'move') {
                if ($element['id'] != $_GET['element_id']) {
                    $display .= '		<li><a href="'.$_SERVER['PHP_SELF'].'?moveto='.$element['position'].'&amp;element_id='.(int)$_GET['element_id'].'&amp;id='.$element['section_id'].'">Sæt ind før</a></li>';
                    $position_after = $element['position'] + 1;
                    $display .= '		<li><a href="'.$_SERVER['PHP_SELF'].'?moveto='.$position_after.'&amp;element_id='.(int)$_GET['element_id'].'&amp;id='.$element['section_id'].'">Sæt ind efter</a></li>';
                }
                else {
                    $display .= '		<li><a href="'.$_SERVER['PHP_SELF'].'?id='.$element['section_id'].'">Fortryd</a></li>';
                }
            }
            else {
                $display .= '		<li><a href="'.$_SERVER['PHP_SELF'].'?action=move&amp;element_id='.$element['id'].'&amp;id='.$element['section_id'].'">Flyt</a></li>';
            }
            $display .= '		<li><a class="confirm" href="' . $_SERVER['PHP_SELF'] . '?delete='.$element['id'].'">Slet</a></li>';
            $display .=	'	</ul>';
            $display .= '<div>' . 			$output . '</div>';
            $display .= '</div>';
        }
        return $display;
    }

}

?>