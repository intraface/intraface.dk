<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Setting.php';

class SettingTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = FALSE;

    function createSetting()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        return new Intraface_Setting($intranet_id, $user_id);
    }

    function testConstructionOfUser()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 1000000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $this->assertTrue(is_object($setting));
    }

    function testThatSetCanSetSettingsWhichAreDifferentFromUserAndIntranetAndSystem()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);

        $setting->set('user', 'rows_pr_page', 10);
        $setting->set('intranet', 'rows_pr_page', 15);
        $this->assertEquals(20, $setting->get('system', 'rows_pr_page'));
        $this->assertEquals(15, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEquals(10, $setting->get('user', 'rows_pr_page'));
    }

    function testSetAreAbleToRepeatinglySetUserSettingToANewSetting()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $setting->set('user', 'rows_pr_page', 10);
        $setting->set('intranet', 'rows_pr_page', 15);
        $this->assertEquals(20, $setting->get('system', 'rows_pr_page'));
        $this->assertEquals(15, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEquals(10, $setting->get('user', 'rows_pr_page'));
        $setting->set('user', 'rows_pr_page', 30);
        $setting->set('intranet', 'rows_pr_page', 50);
        $this->assertEquals(50, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEquals(30, $setting->get('user', 'rows_pr_page'));
    }

    function testSettingsHaveBeenPersistedSoWeCanRetrieveThemAgainOnTheNextSettingInvocation()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $setting->set('user', 'rows_pr_page', 30);
        $setting->set('intranet', 'rows_pr_page', 50);
        // making sure that it still works next time we get the Setting object
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $this->assertEquals(50, $setting->get('intranet', 'rows_pr_page'));
        $this->assertEquals(30, $setting->get('user', 'rows_pr_page'));
    }

    function testThatYouCanAccessNewlySetSettingsWithGetRightAway()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting1 = new Intraface_Setting($intranet_id, $user_id);
        $first_user_setting = 10;
        $setting1->set('user', 'rows_pr_page', $first_user_setting);
        $this->assertEquals($first_user_setting, $setting1->get('user', 'rows_pr_page'));
    }

    function testSettingIsDifferentWhenSetForDifferntUsers()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting1 = new Intraface_Setting($intranet_id, $user_id);
        $first_user_setting = 10;
        $setting1->set('user', 'rows_pr_page', $first_user_setting);

        $another_user_id = $user_id = rand(10001, 100000);
        $setting2 = new Intraface_Setting($intranet_id, $another_user_id);
        $second_user_setting = 15;
        $setting2->set('user', 'rows_pr_page', $second_user_setting);

        $this->assertEquals($first_user_setting, $setting1->get('user', 'rows_pr_page'));
        $this->assertEquals($second_user_setting, $setting2->get('user', 'rows_pr_page'));
    }

    function testSetReturnsTrueIfItCanSetASetting()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting1 = new Intraface_Setting($intranet_id, $user_id);
        $first_user_setting = 10;
        $this->assertTrue($setting1->set('user', 'rows_pr_page', $first_user_setting));
    }

    function testThrowsExceptionIfAnIvalidSettingIsBeingSet()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting1 = new Intraface_Setting($intranet_id, $user_id);
        $first_user_setting = 10;
        try {
            $setting1->set('user', 'somereallystrangesettingwhichwillneverbevalid', $first_user_setting);
            $this->assertTrue(false, 'An exception should be thrown');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }

    }

    function testThrowsExceptionIfTryingToSetASystemSetting()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting1 = new Intraface_Setting($intranet_id, $user_id);
        $first_user_setting = 10;
        try {
            $setting1->set('system', 'rows_pr_page', $first_user_setting);
            $this->assertTrue(false, 'An exception should be thrown');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }

    }

    function testGetSettingForUserWillReturnIntranetSettingIfUserSettingIsNotSet()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $intranet_setting = 10;
        $setting->set('intranet', 'rows_pr_page', $intranet_setting);
        $this->assertEquals($intranet_setting, $setting->get('user', 'rows_pr_page'));
    }

    function testGetSettingsReturnsAnArray()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $this->assertEquals(0, count($setting->getSettings()));
        $setting->set('user', 'rows_pr_page', 10);
        $this->assertTrue(is_array($setting->getSettings()));
    }

    function testIsSettingSetReturnsFalseIfSettingIsNotSet()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $this->assertFalse($setting->isSettingSet('user', 'rows_pr_page'));
    }

    function testIsSettingSetReturnsTrueIfSettingIsSet()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $setting->set('user', 'rows_pr_page', 10);
        $this->assertTrue($setting->isSettingSet('user', 'rows_pr_page'));
    }

    function testDeleteReturnsTrueIfItCanDeleteASettingAndSettingIsNotSetAnymoreAndCannotBeRetrieved()
    {
        $intranet_id = rand(1, 1000000);
        $user_id = rand(1, 10000);
        $setting = new Intraface_Setting($intranet_id, $user_id);
        $user_setting = 10;
        $setting->set('user', 'rows_pr_page', $user_setting);
        $this->assertTrue($setting->delete('user', 'rows_pr_page'));
        $this->assertFalse($setting->isSettingSet('user', 'rows_pr_page'));
        $this->assertNotEquals($user_setting, $setting->get('user', 'rows_pr_page'));
    }
}
?>