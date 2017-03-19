<?php

class Intraface_modules_product_Variation_OneAttributeGroup extends Intraface_modules_product_Variation
{
    
    public function setUp()
    {
        parent::setUp();
        
        $this->hasOne(
            'Intraface_modules_product_Variation_X_Attribute as attribute1',
            array('local' => 'id', 'foreign' => 'product_variation_id')
        );
    }
    
    public function setAttributesFromArray($input)
    {
        $this->attribute1->product_attribute_id = $input['attribute1'];
        $this->attribute1->attribute_number = 1;
    }
    
    public function getName()
    {
        return $this->attribute1->attribute->group->getName().': '.
            $this->attribute1->attribute->getName();
    }
    
    /**
     * Returns attributes with attribute number as key
     */
    public function getAttributesAsArray()
    {
        return array(
            1 => array(
                'group_id' => $this->attribute1->attribute->group->getId(),
                'id' => $this->attribute1->attribute->getId()
            )
        );
    }
}
