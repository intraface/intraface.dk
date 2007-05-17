<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Setting.php';

class SettingTest extends PHPUnit_Framework_TestCase
{

    function testConstructionOfUser()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 1000000);
        $setting = new Setting($intranet_id, $user_id);
        $this->assertTrue(is_object($setting));
    }

    function testSetAndGetUserSetting()
    {
        $this->markTestIncomplete('needs updating');

        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Setting($intranet_id, $user_id);
        $setting->set('user', 'rows_pr_page', 10);
        $setting->set('intranet', 'rows_pr_page', 15);
        $this->assertEqual(20, $setting->get('system', 'rows_pr_page'));
        $this->assertEqual(15, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEqual(10, $setting->get('user', 'rows_pr_page'));
    }

    function testRepeatingSetAndGetUserSetting()
    {
        $this->markTestIncomplete('needs updating');

        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Setting($intranet_id, $user_id);
        $setting->set('user', 'rows_pr_page', 10);
        $setting->set('intranet', 'rows_pr_page', 15);
        $this->assertEqual(20, $setting->get('system', 'rows_pr_page'));
        $this->assertEqual(15, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEqual(10, $setting->get('user', 'rows_pr_page'));
        $setting->set('user', 'rows_pr_page', 30);
        $setting->set('intranet', 'rows_pr_page', 50);
        $this->assertEqual(50, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEqual(30, $setting->get('user', 'rows_pr_page'));
        // making sure that it still works next time we get the Setting object
        $setting = new Setting($intranet_id, $user_id);
        $this->assertEqual(50, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEqual(30, $setting->get('user', 'rows_pr_page'));
    }

    function testSettingIsEitherForLoggedInUserOrIntranet()
    {
        $this->markTestIncomplete('needs updating');

        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting1 = new Setting($intranet_id, $user_id);
        $first_user_setting = 10;
        $setting1->set('user', 'rows_pr_page', $first_user_setting);
        $another_user_id = $user_id = rand(10001, 100000);
        $setting2 = new Setting($intranet_id, $another_user_id);
        $second_user_setting = 15;
        $setting2->set('user', 'rows_pr_page', $second_user_setting);
        $this->assertEqual($first_user_setting, $setting1->get('user', 'rows_pr_page'));
        $this->assertEqual($second_user_setting, $setting2->get('user', 'rows_pr_page'));

    }
}
?>