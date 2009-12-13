<?php
class Intraface_modules_onlinepayment_OnlinePaymentGateway
{
    protected $kernel;
    protected $fallback_provider;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $implemented_providers = OnlinePayment::getImplementedProviders();
        // we set the fallback from settings
        if (!isset($implemented_providers[$kernel->getSetting()->get('intranet', 'onlinepayment.provider_key')])) {
            throw new Exception('Ikke en gyldig provider fra settings i OnlinePayment->factory');
        }
        $this->fallback_provider = $implemented_providers[$kernel->getSetting()->get('intranet', 'onlinepayment.provider_key')];
    }

    public function findBySettings()
    {
        return $this->getProvider($this->fallback_provider);
    }

    public function findById($value)
    {
        $type = 'id';
        $db = new DB_Sql;
        $db->query("SELECT provider_key FROM onlinepayment WHERE id = ".(int)$value. " AND intranet_id = " . $kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            throw new Exception('OnlinePayment::factory: Ikke et gyldigt id');
        }
        $provider = $this->fallback_provider;
        return $this->getProvider($provider, $value);
    }

    public function findByProvider($provider)
    {
        if (!in_array($value, $this->implemented_providers)) {
            throw new Exception('Ikke en gyldig provider i OnlinePayment->factory case: provider');
        }

        return $this->getProvider($provider);
    }

    public function findByTransactionNumber($value)
    {
        $db = new DB_Sql;
        $db->query("SELECT provider_key FROM onlinepayment WHERE transaction_number = '".$value."' AND intranet_id = " . $kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            throw new Exception('OnlinePayment::factory: Ikke et gyldigt transactionnumber');
        }
        $provider = $implemented_providers[$db->f('provider_key')];

        return $this->getProvider($provider, $value);
    }

    protected function getProvider($provider, $value = 0)
    {
        switch(strtolower($provider)) {
            case 'default':
                require_once 'Intraface/modules/onlinepayment/provider/Default.php';
                return new OnlinePaymentDefault($this->kernel, $value);
                break;
            case 'quickpay':
                require_once 'Intraface/modules/onlinepayment/provider/QuickPay.php';
                return new OnlinePaymentQuickPay($this->kernel, $value);
                break;
            case 'dandomain':
                require_once 'Intraface/modules/onlinepayment/provider/DanDomain.php';
                return new OnlinePaymentDanDomain($this->kernel, $value);
                break;
            default:
                throw new Exception("Ugyldig onlinebetalingsudbyder");
                break;
        }
    }
}