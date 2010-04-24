<?php
require_once 'HTMLPurifier.auto.php';

class Intraface_Validate_Html extends Zend_Validate_Abstract
{
    public function isValid($value)
    {
        $allowed_tags = array('p', 'ul', 'li', 'a', 'ol', 'h3', 'h4');
        $allowed_attr = array('src');
        //$filter = new Zend_Filter_StripTags($allowed_tags, $allowed_attr);

        $filtered = strip_tags($value, '<p><ul><a><ol><li><h3><h4>');
        if ($value == $filtered) {
            return true;
        }
        return false;
    }
}

class Intraface_Filter_HTMLPurifier implements Zend_Filter_Interface
{
    protected $_htmlPurifier = null;

    public function __construct($options = null)
    {
        $config = null;
        if (!is_null($options)) {
            $config = HTMLPurifier_Config::createDefault();
                foreach ($options as $option) {
                    $config->set($option[0] . '.' . $option[1], $option[2]);
                }
        }
        $this->_htmlPurifier = new HTMLPurifier($config);
    }

    public function filter($value)
    {
        return $this->_htmlPurifier->purify($value);
    }

}

class Intraface_Filter_HtmlBody extends Intraface_Filter_HTMLPurifier
{
    public function __construct($newOptions = null)
    {
        $options = array(
            array('Cache', 'SerializerPath',
                PATH_CACHE . '/htmlpurifier'
            ),
            //array('HTML', 'Doctype', 'XHTML 1.0 Strict'),
            array('HTML', 'Allowed',
                'p,em,h1,h2,h3,h4,h5,strong,a[href],ul,ol,li,code,pre,'
                .'blockquote,img[src|alt|height|width],sub,sup,dl,dd,dt'
            ),
            //array('AutoFormat', 'Linkify', 'true'),
            //array('AutoFormat', 'AutoParagraph', 'true')
        );

        if (!is_null($newOptions)) {
            // I'll let HTMLPurifier overwrite original options
            // with new ones rather than filter them myself
                $options = array_merge($options, $newOptions);
        }

        parent::__construct($options);
    }

}

class Intraface_modules_shop_Controller_Index extends k_Component
{
    protected $template;
    protected $error;
    public $input;
    protected $doctrine;

    function __construct(k_TemplateFactory $template, Doctrine_Connection_Common $doctrine)
    {
         $this->template = $template;
         $this->doctrine = $doctrine;
    }

    function map($name)
    {
        /**
         * Not finished. Can be removed if no costumers no longer interested
        if ($name == 'discount-campaigns') {
            return 'Intraface_modules_shop_Controller_DiscountCampaigns';
        } */

        return 'Intraface_modules_shop_Controller_Show';
    }

    function isValid()
    {
        if (!$this->body()) {
            return true;
        }

        $filters = array(
            'receipt' => new Intraface_Filter_HtmlBody(), // should probably only be used on outputting
        );
        $validators = array(
            'show_online' => new Zend_Validate_Digits(),
            'name' => new Zend_Validate_Alnum(true),
            'identifier' => new Zend_Validate_Alnum(),
            'language_key' => new Zend_Validate_Alnum(),
            'show_online' => new Zend_Validate_Alnum(),
            'send_confirmation' => new Zend_Validate_Digits(),
            'confirmation_subject' => array(
                new Zend_Validate_Alnum(),
                'allowEmpty' => true
            ),
            'confirmation' => array(
                new Zend_Validate_Alnum(),
                'allowEmpty' => true
            ),
            'confirmation_add_contact_url' => new Zend_Validate_Digits(),
            'payment_link' => array(
                new Zend_Validate_Callback(array('Zend_Uri', 'check')),
                'allowEmpty' => true

            ),
            'payment_link_add' => new Zend_Validate_Digits(),
            'confirmation_greeting' => array(
                new Zend_Validate_Alnum(),
                'allowEmpty' => true
            ),
            'terms_of_trade_url' => array(
                new Zend_Validate_Callback(array('Zend_Uri', 'check')),
                'allowEmpty' => true

            ),
            'receipt' => array(
                new Intraface_Validate_Html(),
                'allowEmpty' => true
            )
        );
        $data = $this->body();

        $this->input = new Zend_Filter_Input($filters, $validators, $data);
        return $this->input->isValid();
    }

    function renderHtml()
    {
        $this->document->setTitle('Shops');
        $this->document->options = array($this->url(null, array('create')) => 'Create');

        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findByIntranetId($this->getKernel()->intranet->getId());

        if (count($shops) == 0) {
            $tpl = $this->template->create(dirname(__FILE__) . '/tpl/empty-table');
            return $tpl->render($this, array('message' => 'No shops has been created yet.'));
        }

        $data = array('shops' => $shops);
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/shops');
        return $tpl->render($this, $data);
    }

    function renderHtmlCreate()
    {
        $this->document->setTitle('Create shop');

        $data = array();

        if (!$this->isValid()) {
            $data = $this->body();
        } else {
            $data['receipt'] = $this->getKernel()->setting->get('intranet','webshop.webshop_receipt');
        }

        if ($this->getKernel()->intranet->hasModuleAccess('currency')) {
            $this->getKernel()->useModule('currency', true); // true: ignore user access
            $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
            try {
                $currencies = $gateway->findAllWithExchangeRate();
            } catch (Intraface_Gateway_Exception $e) {
                $currencies = array();
            }
        } else {
            $currencies = false;
        }

        $webshop_module = $this->getKernel()->module('shop');
        $settings = $webshop_module->getSetting('show_online');
        $languages = new Intraface_modules_language_Languages;
        $langs = $languages->getChosenAsArray();

        $data = array(
            'data' => $data,
            'settings' => $settings,
            'currencies' => $currencies,
            'languages' => $langs);
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        if (!$this->isValid()) {
            return $this->render();
        }

        try {
            $shop = new Intraface_modules_shop_Shop;
            $shop->intranet_id = $this->getKernel()->intranet->getId();

            $shop->fromArray($this->input->getUnescaped());
            if ($this->body('confirmation_add_contact_url') == 1) {
                $shop->confirmation_add_contact_url = 1;
            } else {
                $shop->confirmation_add_contact_url = 0;
            }
            if ($this->body('payment_link_add') == 1) {
                $shop->payment_link_add = 1;
            } else {
                $shop->payment_link_add = 0;
            }
            if ($this->body('send_confirmation') == 1) {
                $shop->send_confirmation = 1;
            } else {
                $shop->send_confirmation = 0;
            }
            $shop->save();

            return new k_SeeOther($this->url($shop->getId()));
        } catch (Exception $e) {
            throw $e;
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/content');
        return $tpl->render($this, array('content' => $content));
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function document()
    {
        return $this->document;
    }
}