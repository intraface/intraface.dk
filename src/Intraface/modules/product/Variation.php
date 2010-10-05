<?php
class Intraface_modules_product_Variation extends Doctrine_Record
{
    private $stock;

    public function setTableDefinition()
    {
        $this->setTableName('product_variation');
        $this->hasColumn('product_id', 'integer', 11, array());
        $this->hasColumn('number', 'integer', 11, array());
    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->actAs('SoftDelete');

        $this->hasMany('Intraface_modules_product_Variation_Detail as detail',
            array('local' => 'id', 'foreign' => 'product_variation_id'));
    }

    public function preInsert($event)
    {
        $collection = $this->getTable()
            ->createQuery()
            ->select('number')
            ->addWhere('product_id = '.$this->product_id)
            ->addWhere(get_class($this).'.deleted_at IS NULL OR '.get_class($this).'.deleted_at IS NOT NULL')
            ->orderBy('number')
            ->execute();

        if ($collection == NULL || $collection->count() == 0) {
            $this->number = 1;
        } else {
            $this->number = $collection->getLast()->getNumber() + 1;
        }

    }
    
    /**
     * Returns detail object
     * 
     * @param $id
     * @return object Intraface_modules_product_Variation_Detail
     */
    public function getDetail($id = 0)
    {
        if ($this->id === NULL) {
            throw new Exception('You can first get detail when variation is saved');
        }
        
        if ($id != 0) {
            foreach ($this->detail AS $detail) {
                if ($detail->getId() == $id) {
                    return $detail;
                }
            }
            throw new Exception('Unable to find detail with id '.$id);
        } elseif ($this->detail->count() > 0) {
            return $this->detail->getFirst();
        } else {
            $detail = new Intraface_modules_product_Variation_Detail;
            $detail->product_variation_id = $this->getId();
            return $detail;
        }

        /*
        $collection = $this->detail;
        if ($collection->count() == 0) {
            $detail = new Intraface_modules_product_Variation_Detail;
            $detail->product_variation_id = $this->getId();
            return $detail;
        } elseif ($id != 0) {
            $collection = $this->detail[0]->getTable()
                ->createQuery()
                ->select('*')
                ->addWhere('id = ?', array($id))
                ->addWhere('product_variation_id = ?', array($this->id))
                ->execute();
            if ($collection == NULL || $collection->count() == 0) {
                throw new Intraface_Gateway_Exception('Invalid variation detail id '.$id);
            }
            return $collection->getFirst();

        }
        $collection = $this->detail[0]->getTable()
            ->createQuery()
            ->select('*')
            ->addWhere('product_variation_id = ?', array($this->id))
            ->orderBy('date_created DESC')
            ->execute();
        return $collection->getFirst();
        */
    }

    public function getStock($product = NULL)
    {
        if (!$this->stock) {
            if ($product->getId() != $this->product_id) {
                throw new Exception('The given product does not match the variation');
            }

            if ($product == NULL) {
                throw new Exception('You have to provide the correct Product object the first time you request stock');
            }

            require_once 'Intraface/modules/stock/Stock.php';
            $this->stock = new Stock($product, $this);
        }
        return $this->stock;
    }

    public function setAttributesFromArray($input)
    {
        throw new Exception('To be overridden!');
    }

    /**
     * Returns attributes with attribute number as key
     *
     * @return array Attributes with attribute number as key
     */
    public function getAttributesAsArray()
    {
        throw new Exception('To be overridden!');
    }

    public function getName()
    {
        throw new Exception('To be overridden!');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNumber()
    {
        return $this->number;
    }
}
