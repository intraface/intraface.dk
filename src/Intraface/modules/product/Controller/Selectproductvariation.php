<?php
class Intraface_modules_product_Controller_Selectproductvariation extends k_Component
{
    protected $product;
    public $multiple = false;
    public $quantity = false;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        $this->quantity = $this->query('set_quantity');
        $this->multiple = $this->query('multiple');
        $this->url_state->set('set_quantity', $this->query('set_quantity'));
        $this->url_state->set('multiple', $this->query('multiple'));
        $this->url_state->set('use_stored', 'true');
        return parent::dispatch();
    }

    function renderHtml()
    {
        $product_module = $this->getKernel()->module("product");

        $product = $this->context->getProduct();

        if ($this->query('edit_product_variation')) {
            $add_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $add_redirect->setIdentifier('add_new');
            $url = $add_redirect->setDestination($product_module->getPath().'product_edit.php', $product_module->getPath().'select_product.php?'.$redirect->get('redirect_query_string').'&set_quantity='.$this->quantity);
            return new k_SeeOther($url);
        }

        if (!$product->get('has_variation')) {
            throw new Exception('The product is not with variations');
        }

        try {
            $variations = $product->getVariations();
        } catch (Exception $e) {
            if ($e->getMessage() == 'No groups is added to the product') {
                $variations = array();
            } else {
                throw $e;
            }
        }

        $data = array(
            'variations' => $variations,
            'product' => $product
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/selectproductvariation');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $product = $this->getProduct();

        if ($this->body('submit') || $this->body('submit_close')) {
            if ($this->multiple && is_array($this->body('selected'))) {
                foreach ($this->body('selected') as $selected_id => $selected_value) {
                    if ((int)$selected_value > 0) {
                        $selected = array('product_id' => $product->getId(), 'product_variation_id' => $selected_id);
                        //$this->context->context->removeItem($selected);
                        if ($this->quantity) {
                            $this->context->context->addItem($selected, $selected_value);
                        } else {
                            $this->context->context->addItem($selected);
                        }
                    }
                }
            } elseif (!$this->multiple && $this->body('selected')) {
                $selected = array('product_id' => $product->getId(), 'product_variation_id' => (int)$this->body('selected'));
                if ($this->quantity) {
                    $this->context->context->addItem($selected, (int)$this->body('quantity'));
                } else {
                    $this->context->context->addItem($selected);
                }
            }

            if ($this->body('submit_close')) {
                return new k_SeeOther($this->url('../../'));
            }
            return new k_SeeOther($this->url());
        }
        return $this->render();
    }

    function getProducts()
    {
        if ($this->query("search") || $this->query("keyword_id")) {
            if ($this->query("search")) {
                $this->getProduct()->getDBQuery()->setFilter("search", $this->query("search"));
            }

            if ($this->query("keyword_id")) {
                $this->getProduct()->getDBQuery()->setKeyword($this->query("keyword_id"));
            }
        } else {
            $this->getProduct()->getDBQuery()->useCharacter();
        }

        $this->getProduct()->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $this->getProduct()->getDBQuery()->usePaging("paging");
        $this->getProduct()->getDBQuery()->storeResult("use_stored", "select_product", "sublevel");
        $this->getProduct()->getDBQuery()->setExtraUri('set_quantity='.$this->quantity);

        return $this->getProduct()->getList();
    }

    function getProduct()
    {
        if (is_object($this->product)) {
            return $this->product;
        }
        return $this->product = new Product($this->getKernel(), $this->context->name());
    }

    function getKeywords()
    {
        return $keywords = $this->getProduct()->getKeywordAppender();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getRedirect()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
        if ($redirect->get('id') != 0) {
            $this->multiple = $redirect->isMultipleParameter('product_variation_id');
        } else {
            throw new Exception("Der mangler en gyldig redirect");
        }
        return $redirect;
    }
}
