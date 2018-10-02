<?php

class Olegnax_Osc_Model_Backend_Controls_Twocolumn
{
    const OPTION_FORTWO_FIRST     = 'column_2-first';
    const OPTION_FORTWO_SECOND    = 'column_2-second';
    const OPTION_FORTWO_THIRD     = 'column_2-third';    
   
    public function toOptionArray()
    {
        return array(array('value' => self::OPTION_FORTWO_FIRST, 'label' => Mage::helper('olegnax_osc')->__('First Layout for 2 Columns')), array('value' => self::OPTION_FORTWO_SECOND, 'label' => Mage::helper('olegnax_osc')->__('Second Layout for 2 Columns')), array('value' => self::OPTION_FORTWO_THIRD, 'label' => Mage::helper('olegnax_osc')->__('Third Layout for 2 Columns')));
    }
}
