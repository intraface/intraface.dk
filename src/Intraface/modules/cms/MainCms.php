<?php
/**
 * @package Intraface_CMS
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
class MainCMS extends Intraface_Main
{
    function __construct()
    {
        $this->module_name     = 'cms'; // Navnet der vil st� i menuen
        $this->menu_label      = 'CMS'; // Navnet der vil st� i menuen
        $this->show_menu       = 1; // Skal modulet vises i menuen.
        $this->active          = 1; // Er modulet aktivt.
        $this->menu_index      = 10;
        $this->frontpage_index = 70;

        //$this->addControlPanelFile('accounting', $this->getPath() . 'settings.php');

        // Tilf�j undermenu punkter.
        $this->addSubMenuItem('choose site', '');
        //$this->addSubMenuItem('pages', 'pages?type=page');
        //$this->addSubMenuItem('articles', 'pages?type=article');
        //$this->addSubMenuItem('news', 'pages?type=news');

        // Tilf�j subaccess punkter
        $this->addSubAccessItem('edit_stylesheet', 'stylesheet');
        $this->addSubAccessItem('edit_templates', 'templates');

        // $this->addDependentModule('filemanager');

        $this->addPreloadFile('Site.php');
        $this->addPreloadFile('Page.php');
        $this->addPreloadFile('Element.php');
        $this->addPreloadFile('Parameter.php');
        $this->addPreloadFile('Navigation.php');
        $this->addPreloadFile('Stylesheet.php');
        $this->addPreloadFile('Template.php');
        $this->addPreloadFile('TemplateSection.php');
        $this->addPreloadFile('SiteMap.php');
        $this->addPreloadFile('HTML_Parser.php');

        $this->addPreloadFile('Section.php');

         $this->includeSettingFile('settings.php');

        $this->addSetting('status', array(
            1 => 'draft',
            2 => 'hidden',
            3 => 'published'
            )
        );

        $this->addSetting('element_types', array(
            1 => 'htmltext',
            2 => 'picture',
            3 => 'flickr', // og 23 hq
            4 => 'delicious',
            5 => 'pagelist',
            6 => 'filelist',
            7 => 'gallery',
            8 => 'video', // revver og google
            9 => 'map',
            //10 => 'shorttext',
            //11 => 'longtext',
            12 => 'wikitext'
            )
        );

        $this->addSetting('section_types', array(
            0 => '_invalid_',
            1 => 'shorttext',
            2 => 'longtext',
            3 => 'picture',
            4 => 'mixed',
            5 => 'gallery' // m�ske skal man s�tte allowed elements?
        ));

        $this->addSetting('htmleditors', array(
            'tinymce' => 'tinymce',
            'none'    => 'none',
            'wiki'    => 'wiki'
        ));

        $_cc_license = array();
        require PATH_INCLUDE_CONFIG.'setting_cc_license.php';
        $this->addSetting('cc_license', $_cc_license);
    }
}