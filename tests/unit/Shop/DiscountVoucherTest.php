<?php
/**
 * Not finished. Can be removed if costumers no longer interested 6/3 2010 /Sune
 */
Intraface_Doctrine_Intranet::singleton(1);

class DicountVoucherTest extends PHPUnit_Framework_TestCase
{
    private $webshop;
    private $kernel;
    protected $backupGlobals = false;

    function setUp()
    {
        $connection = Doctrine_Manager::connection();
        $connection->exec('TRUNCATE shop_dicount_campaign');
    }

    function getDicountCampaign($id = 0)
    {
        if ($id != 0) {
            $gateway = new Intraface_modules_shop_DiscountCampaignGateway(Doctrine_Manager::connection(), null);
            return $gateway->findById($id);
        }
        return new Intraface_modules_shop_DiscountCampaign;
    }

    ////////////////////////////////////////////////

    function testConstruction()
    {
        $campaign = $this->getDicountCampaign();
        $this->assertEquals('Intraface_modules_shop_DiscountCampaign', get_class($campaign));
    }

    function testSaveCampaign()
    {
        $campaign = $this->getDicountCampaign();
        $campaign->name = 'Test';
        $campaign->voucher_code_prefix = 'test';
        $campaign->save();

        $campaign->refresh();
        $this->assertEquals('Test', $campaign->getName());
        $this->assertEquals(1, $campaign->getId());
    }

    function testFindCampaignFromId()
    {
        $campaign = $this->getDicountCampaign();
        $campaign->name = 'Test';
        $campaign->voucher_code_prefix = 'test';
        $campaign->save();

        $campaign = $this->getDicountCampaign($campaign->getId());

        $this->assertEquals('Test', $campaign->getName());
        $this->assertEquals(1, $campaign->getId());
    }

    function testSaveCoupon()
    {

    }
}
