<?php
class Olegnax_Osc_Model_Backend_Controls_Threecolumn {

    const OPTION_FORTHREE_FIRST     = 'column_3-first';
    const OPTION_FORTHREE_SECOND    = 'column_3-second';
    const OPTION_FORTHREE_THIRD     = 'column_3-third';    
    
    public function toOptionArray()
    {
        return array(array('value' => self::OPTION_FORTHREE_FIRST, 'label' => Mage::helper('olegnax_osc')->__('First Layout for 3 Columns')), array('value' => self::OPTION_FORTHREE_SECOND, 'label' => Mage::helper('olegnax_osc')->__('Second Layout for 3 Columns')), array('value' => self::OPTION_FORTHREE_THIRD, 'label' => Mage::helper('olegnax_osc')->__('Third Layout for 3 Columns')));
    }
}
?>
