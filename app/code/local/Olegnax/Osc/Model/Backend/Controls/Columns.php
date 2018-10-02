<?php
class Olegnax_Osc_Model_Backend_Controls_Columns
{
    const OPTION_ONE_COLUMN     = 'column_1';
    const OPTION_TWO_COLUMNS    = 'columns_2';
    const OPTION_THREE_COLUMNS    = 'columns_3';    
    
    public function toOptionArray()
    {
        return array(array('value' => self::OPTION_ONE_COLUMN, 'label' => Mage::helper('olegnax_osc')->__('1 Column')), array('value' => self::OPTION_TWO_COLUMNS, 'label' => Mage::helper('olegnax_osc')->__('2 Columns')), array('value' => self::OPTION_THREE_COLUMNS, 'label' => Mage::helper('olegnax_osc')->__('3 Columns')));
    }
}
?>
