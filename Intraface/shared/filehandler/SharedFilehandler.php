<?php
/**
 *
 * @package Intraface
 * @author	<Sune>
 * @since	1.0
 * @version	1.0
 *
 */

class Sharedfilehandler Extends Shared {

    function sharedfilehandler() {
        $this->shared_name = 'filehandler'; // Navn på på mappen med modullet
        $this->active = 1; // Er shared aktivt

        $_file_type = array();
        require_once(PATH_INCLUDE_CONFIG.'setting_file_type.php');
        $this->addSetting('file_type', $_file_type);

        $this->addSetting('accessibility', array(
            0 => '_invalid_',
            1 => 'user',
            2 => 'intranet',
            3 => 'public'));

        $this->addSetting('file_append_belong_to_types', array(
            0 => '_invalid_',
            1 => 'cms_element_gallery',
            2 => 'procurement_procurement',
            3 => 'product',
            4 => 'cms_element_filelist'));

        /*
        // Moved to InstanceHandler 
        $this->addSetting('instance_types', array(
            0 => array('name' => 'manual'), // Manuelt størrelse
            1 => array('name' => 'square', 'max_width' => 75, 'max_height' => 75),
            2 => array('name' => 'thumbnail', 'max_width' => 100, 'max_height' => 67),
            3 => array('name' => 'small', 'max_width' => 240, 'max_height' => 160),
            4 => array('name' => 'medium', 'max_width' => 500, 'max_height' => 333),
            5 => array('name' => 'large', 'max_width' => 1024, 'max_height' => 683),
            6 => array('name' => 'website', 'max_width' => 780, 'max_height' => 550)));
        */

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile('FileHandler.php');
        $this->addPreloadFile('FileHandlerHTML.php');

        // Fil til med indstillinger man kan sætte i modullet
        // $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes på forsiden.
        // $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["shared_navn.setting"] = "Værdi";
        $this->includeSettingFile('settings.php');
    }
}

?>