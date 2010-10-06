<?php
/**
 * @package Intraface_Product
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
class MainProduct extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'product'; // Navnet der vil st� i menuen
        $this->menu_label = 'Produkter'; // Navnet der vil st� i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 150;
        $this->frontpage_index = 30;

        $this->addPreloadFile('Product.php');
        $this->addPreloadFile('ProductDetail.php');

        $this->addRequiredShared('keyword');

        /*
        // hvilke units kan man v�lge imellem?
        $this->addSetting('unit', array(1 => '',
                                        2 => 'stk.',
                                        3 => 'dag(e)',
                                        4 => 'm�ned(er)',
                                        5 => '�r',
                                        6 => 'time(r)'));
        */
        $this->includeSettingFile('settings.php');

        // i �jeblikket er der ingen relevante settings at lave her
        //$this->addControlpanelFile('Produkter', '/modules/product/setting.php');

        $this->addFrontpageFile('include_frontpage.php');

        // $this->addSubmenuItem('attributes', 'attributes.php', 'module:webshop');
    }
}
