<?php
class Intraface_modules_product_Controller_Selectproductvariation extends k_Component
{
    protected $product;

    function getKernel()
    {
        return $this->context->getKernel();
    }

    public $multiple;
    public $quantity;

    function getRedirect()
    {
        return $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function renderHtml()
    {
        $product_module = $this->getKernel()->module("product");
        $translation = $this->getKernel()->getTranslation('product');

        if (isset($_GET['set_quantity']) && (int)$_GET['set_quantity'] == 1) {
            $quantity = 1;
        } else {
            $quantity = 0;
        }
        $product = new Product($this->getKernel(), intval($this->context->name()));

        if (isset($_GET['edit_product_variation'])) {
            $add_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $add_redirect->setIdentifier('add_new');
            $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$redirect->get('redirect_query_string').'&set_quantity='.$quantity);
            header('location: '.$url);
            exit;
        }

        if (!$product->get('has_variation')) {
            throw new Exception('The product is not with variations');
        }

        try {
            $variations = $product->getVariations();
        } catch(Exception $e) {
            if ($e->getMessage() == 'No groups is added to the product') {
                $variations = array();
            } else {
                throw $e;
            }
        }
        $smarty = new k_Template(dirname(__FILE__) . '/tpl/selectproductvariation.tpl.php');
        return $smarty->render($this, array('variations' => $variations));
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

    function t($phrase)
    {
        return $phrase;
    }


    function postForm()
    {
        if (isset($_POST['set_quantity']) && (int)$_POST['set_quantity'] == 1) {
            $quantity = 1;
        } else {
            $quantity = 0;
        }

        if (empty($_POST['product_id'])) {
            throw new Exception('You need to provide a product_id');
        }

        $product = new Product($this->getKernel(), intval($_POST['product_id']));

        if (isset($_POST['submit']) || isset($_POST['submit_close'])) {
            if ($multiple && is_array($_POST['selected'])) {
                foreach ($_POST['selected'] AS $selected_id => $selected_value) {
                    if ((int)$selected_value > 0) {
                        $selected = serialize(array('product_id' => $product->getId(), 'product_variation_id' => $selected_id));
                        // Hvis der allerede er gemt en værdi, så starter vi med at fjerne den, så der ikke kommer flere på.
                        $redirect->removeParameter('product_variation_id', $selected);
                        if ($quantity) {
                            $redirect->setParameter('product_variation_id', $selected, $selected_value);
                        } else {
                            $redirect->setParameter('product_variation_id', $selected);
                        }
                    }
                }
            } elseif (!$multiple && !empty($_POST['selected'])) {
                $selected = serialize(array('product_id' => $product->getId(), 'product_variation_id' => (int)$_POST['selected']));
                if ($quantity) {
                    $redirect->setParameter('product_variation_id', $selected, (int)$_POST['quantity']);
                } else {
                    $redirect->setParameter('product_variation_id', $selected);
                }
            }

            if (isset($_POST['submit_close'])) {
                header('location: '.$redirect->getRedirect('index.php'));
                exit;
            }
        }

    }
}