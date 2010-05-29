<?php
/**
 * This class is the univeral variation class wich is usued with all numbers of attributes to join.
 * The class has no functionality to add or get attributes
 * 
 * 
 * @author Sune Jensen <sune@intraface.dk>
 *
 */
class Intraface_modules_product_Variation_UniversalAttributeGroups extends Intraface_modules_product_Variation
{
    
    
    public function setUp()
    {
        parent::setUp();
        
        $this->hasOne('Intraface_modules_product_Variation_X_Attribute as attribute1', 
            array('local' => 'id', 'foreign' => 'product_variation_id'));
        
        $this->hasOne('Intraface_modules_product_Variation_X_Attribute as attribute2', 
            array('local' => 'id', 'foreign' => 'product_variation_id'));
    }
    
    public function setAttributesFromArray($input)
    {
        throw new Exception('Use variation with specific number of attribute groups');
    }
    
    /**
     * Returns attributes with attribute number as key
     */
    public function getAttributesAsArray()
    {
        throw new Exception('Use variation with specific number of attribute groups');
    }

    public function getName()
    {
        throw new Exception('Use variation with specific number of attribute groups');
    }
    
}

?>