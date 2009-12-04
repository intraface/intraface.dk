<?php
class Intraface_modules_product_Controller_Selectproduct extends Intraface_modules_product_Controller_Index
{
    protected $product;
    public $multiple;
    public $quantity;
    public $selected_products;

    function __construct()
    {
        $this->multiple = $this->query('multiple');
        $this->quantity = $this->query('set_quantity');
    }

    function renderHtml()
    {
        $product_module = $this->getKernel()->module("product");
        $translation = $this->getKernel()->getTranslation('product');

        if (isset($_GET['add_new'])) {
            $add_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $add_redirect->setIdentifier('add_new');
            $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$this->getRedirect()->get('redirect_query_string').'&set_quantity='.$this->quantity);
            $add_redirect->askParameter('product_id');
            return new k_SeeOther($url);
        }

        if (!empty($_GET['select_variation'])) {
            $variation_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $variation_redirect->setIdentifier('select_variation');
            $url = $variation_redirect->setDestination($product_module->getPath().'select_product_variation.php?product_id='.intval($_GET['select_variation']).'&set_quantity='.$this->quantity, $product_module->getPath().'select_product.php?'.$this->getRedirect()->get('redirect_query_string').'&set_quantity='.$this->quantity);
            $type = ($this->multiple) ? 'multiple' : 'single';
            $variation_redirect->askParameter('product_variation_id', $type);
            return new k_SeeOther($url);
        }

        if (isset($_GET['return_redirect_id'])) {
            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($return_redirect->getIdentifier() == 'add_new' && $return_redirect->getParameter('product_id') != 0) {
                $redirect->setParameter('product_id', serialize(array('product_id' => intval($return_redirect->getParameter('product_id')), 'product_variation_id' => 0)), 1);
            } elseif ($return_redirect->getIdentifier() == 'select_variation') {
                // Returning from variations page and add the variations to the product_id parameter.
                $product_variations = $return_redirect->getParameter('product_variation_id', 'with_extra_value');
                if ($this->multiple) {
                    foreach ($product_variations AS $product_variation) {
                        $redirect->removeParameter('product_id', $product_variation['value']);
                        if ($this->quantity) {
                            $redirect->setParameter('product_id', $product_variation['value'], $product_variation['extra_value']);
                        } else {
                            $redirect->setParameter('product_id', $product_variation['value']);
                        }
                    }
                } else {
                    $redirect->removeParameter('product_id', $product_variations['value']);
                    if ($this->quantity) {
                        $redirect->setParameter('product_id', $product_variations['value'], $product_variations['extra_value']);
                    } else {
                        $redirect->setParameter('product_id', $product_variations['value']);
                    }
                }
            }
        }

        $product = new Product($this->getKernel());
        $keywords = $product->getKeywordAppender();

        $list = $product->getList();

        // @todo where should this get its values from
        $product_values = array();
        $this->selected_products = array();
        if (!empty($product_values) AND is_array($product_values)) {
            if ($this->multiple) {
                foreach ($product_values AS $selection) {
                    $selection['value'] = unserialize($selection['value']);
                    $this->selected_products[$selection['value']['product_id']] = $selection['extra_value'];
                }
            } else {
                $this->selected_products[$product_values['value']['product_id']] = $product_values['extra_value'];
            }
        }

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/selectproduct.tpl.php');
        return $smarty->render($this);
    }

    function getRedirect()
    {
        return $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getProducts()
    {
        if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {

            if (isset($_GET["search"])) {
                $this->getProduct()->getDBQuery()->setFilter("search", $_GET["search"]);
            }

            if (isset($_GET["keyword_id"])) {
                $this->getProduct()->getDBQuery()->setKeyword($_GET["keyword_id"]);
            }
        } else {
            $this->getProduct()->getDBQuery()->useCharacter();
        }

        $this->getProduct()->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $this->getProduct()->getDBQuery()->usePaging("paging");
        $this->getProduct()->getDBQuery()->storeResult("use_stored", "select_product", "sublevel");
        $this->getProduct()->getDBQuery()->setExtraUri('set_quantity='.$this->quantity);

        return  $list = $this->getProduct()->getList();
    }

    function getProduct()
    {
        if (is_object($this->product)) {
            return $this->product;
        }
        return $this->product = new Product($this->getKernel());

    }

    function getKeywords()
    {
        return $keywords = $this->getProduct()->getKeywordAppender();
    }

    function putForm()
    {
        if (isset($_POST['submit']) || isset($_POST['submit_close'])) {
            if ($this->multiple) {
                if (isset($_POST['selected']) && is_array($_POST['selected'])) {
                    foreach ($_POST['selected'] AS $selected_id => $selected_value) {
                        if ($selected_value != '' && $selected_value != '0') {
                            $product = array(
                            	'product_id' => $selected_id,
                            	'product_variation_id' => 0);

                            $this->context->addItem($product, $selected_value);
                        }
                    }
                }
            } else {
                if (isset($_POST['selected']) && (int)$_POST['selected'] != 0) {
                    $product = array('product_id' => (int)$_POST['selected'], 'product_variation_id' => 0);
                    $this->context->addItem($product, (int)$_POST['quantity']);
                }
            }

            if (isset($_POST['submit_close'])) {
                return new k_SeeOther($this->url('../'));
            }

            return new k_SeeOther($this->url(null, array('use_stored' => true, 'set_quantity' => $this->quantity, 'multiple' => $this->multiple)));
        }

    }
}