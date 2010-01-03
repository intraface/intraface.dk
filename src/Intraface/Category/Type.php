<?php
/**
 * Local extension of Ilib_Category_Type
 */
class Intraface_Category_Type extends Ilib_Category_Type
{
    /**
     * Constructor
     *
     * @param string  $type
     * @param integer $id
     *
     * @return void
     */
    public function __construct($type, $id = 0)
    {
        switch($type) {
            case 'shop':
                $this->belong_to = 1;
                $this->id = $id;
                break;

            default:
                throw new Exception('invalid type');
                exit;
        }
    }
}