<?php
/**
 * Account
 *
 * @package Intraface_Accounting
 *
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */
class MainAccounting extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'accounting'; // Navnet der vil st� i menuen
        $this->menu_label = 'Regnskab'; // Navnet der vil st� i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 40;
        $this->frontpage_index = 10;

        // Tilf�j undermenu punkter.
        $this->addSubMenuItem('accounting year', 'year');
        $this->addSubMenuItem('daybook', 'daybook');
        //$this->addSubMenuItem('state', 'state.php');
        $this->addSubMenuItem('accounts', 'account');
        $this->addSubMenuItem('vouchers', 'voucher');
        //$this->addSubMenuItem('vat', 'vat', 'sub_access:vat_report');
        //$this->addSubMenuItem('end year', 'end', 'sub_access:endyear');
        $this->addSubMenuItem('search', 'search');
        //$this->addSubMenuItem('settings', 'setting.php', 'sub_access:setting');
        //$this->addSubMenuItem('Hj�lp', 'help.php');

        // Tilf�j subaccess punkter
        $this->addSubAccessItem('endyear', 'Årsafslutning');
        $this->addSubAccessItem('vat_report', 'Momsopgivelse');
        $this->addSubAccessItem('setting', 'Indstillinger');

        $this->addControlPanelFile('accounting settings', 'module/accounting/setting');

        $this->addFrontpageFile('include_frontpage.php');

        $this->addSetting('vat_periods',
            array(
                // halv�rlig
                0 => array(
                    'name' => 'Half-yearly',
                    'periods' => array(
                        // 1. halv�r
                        1 => array(
                            'name' => '1st half year',
                            'date_from' => '01-01',
                            'date_to' => '06-30'
                        ),
                        // 2. halv�r
                        2 => array(
                            'name' => '2nd half year',
                            'date_from' => '07-01',
                            'date_to' => '12-31'
                        )
                    )
                ),
                // kvartalsvis
                1 => array(
                    'name' => 'Quarterly',
                    'periods' => array(
                        // januarkvartal
                        1 => array(
                            'name' => '1st quarter',
                            'date_from' => '01-01',
                            'date_to' => '03-31'
                        ),
                        // februarkvartal
                        2 => array(
                            'name' => '2nd quarter',
                            'date_from' => '04-01',
                            'date_to' => '06-30'
                        ),
                        // februarkvartal
                        3 => array(
                            'name' => '3rd quarter',
                            'date_from' => '07-01',
                            'date_to' => '09-30'
                        ),
                        // februarkvartal
                        4 => array(
                            'name' => '4th quarter',
                            'date_from' => '10-01',
                            'date_to' => '12-31'
                        )
                    )
                )
            )
        );
        $this->includeSettingFile('settings.php');
        $this->addPreloadFile('Account.php');
        $this->addPreloadFile('Year.php');
        $this->addPreloadFile('Post.php');
        $this->addPreloadFile('YearEnd.php');
        $this->addPreloadFile('Voucher.php');
        $this->addPreloadFile('VoucherFile.php');
        $this->addPreloadFile('VatPeriod.php');
    }
}
