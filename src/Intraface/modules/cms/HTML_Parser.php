<?php
/**
 * @package Intraface_CMS
 */
class CMS_HTML_Parser extends Intraface_modules_cms_HTML_Parser
{
    private $translation;

    /**
     * Constructor
     *
     * ATTENTION: this method has to override the parent class why I am not sure, but make
     * sure section_html.php is updated accordingly
     *
     * @param object $translation Translation object
     *
     * @return void
     */
    function __construct($translation)
    {
        $this->translation = $translation;
    }

    function parseRandompictureElement($element)
    {
        return '<img src="'.$element['uri'].'" height="'.$element['height'].'" width="'.$element['width'].'">';
    }

    function parseTwitterElement($element)
    {
        $element = unserialize($element);
        $out = '';
        foreach ($element['results']['results'] as $result) {
            $out .= '<p><img src="'.$result['profile_image_url'].'">'.$result['text'].'</p>';
        }

        return $out;
    }

    function parseElements($elements)
    {
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
            $display .= '		<li><a href="'.url('element/'.$element['id']).'" title="'.$this->translation->get('edit element').'">'.$this->translation->get('edit').'</a></li>';

            if (!empty($_GET['action']) AND $_GET['action'] == 'move') {
                if ($element['id'] != $_GET['element_id']) {
                    $display .= '		<li><a href="'.url(null).'?moveto='.$element['position'].'&amp;element_id='.(int)$_GET['element_id'].'&amp;id='.$element['section_id'].'">'.$this->translation->get('insert before').'</a></li>';
                    $position_after = $element['position'] + 1;
                    $display .= '		<li><a href="'.url(null).'?moveto='.$position_after.'&amp;element_id='.(int)$_GET['element_id'].'&amp;id='.$element['section_id'].'">'.$this->translation->get('insert after').'</a></li>';
                } else {
                    $display .= '		<li><a href="'.url(null).'?id='.$element['section_id'].'">'.$this->translation->get('Cancel').'</a></li>';
                }
            } else {
                $display .= '		<li><a href="'.url(null).'?action=move&amp;element_id='.$element['id'].'&amp;id='.$element['section_id'].'">'.$this->translation->get('move').'</a></li>';
            }
            $display .= '		<li><a class="confirm" href="' . url(null) . '?delete='.$element['id'].'">'.$this->translation->get('delete').'</a></li>';
            $display .=	'	</ul>';
            $display .= '<div>' . 			$output . '</div>';
            $display .= '</div>';
        }
        return $display;
    }
}