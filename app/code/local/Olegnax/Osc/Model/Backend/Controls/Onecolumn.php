<?php

class Olegnax_Osc_Model_Backend_Controls_Onecolumn
{
    const OPTION_FORONE_FIRST     = 'column_1-first';
    const OPTION_FORONE_SECOND    = 'column_1-second';
    const OPTION_FORONE_THIRD     = 'column_1-third';    
    
    public function toOptionArray()
    {
        return array(array('value' => self::OPTION_FORONE_FIRST, 'label' => Mage::helper('olegnax_osc')->__('First Layout for 1 Column')), array('value' => self::OPTION_FORONE_SECOND, 'label' => Mage::helper('olegnax_osc')->__('Second Layout for 1 Column')), array('value' => self::OPTION_FORONE_THIRD, 'label' => Mage::helper('olegnax_osc')->__('Third Layout for 1 Column')));
    }
}
