<?php
class Olegnax_Osc_Model_Backend_Controls_Enableddisabled
{
    const OFF_CODE = 0;
    const ON_CODE  = 1;
    const OFF_LABEL = 'Disabled';
    const ON_LABEL  = 'Enabled';    
    
    public function toOptionArray()
    {
        return array(array('value' => self::ON_CODE, 'label' => Mage::helper('olegnax_osc')->__(self::ON_LABEL)), array('value' => self::OFF_CODE, 'label' => Mage::helper('olegnax_osc')->__(self::OFF_LABEL)));
    }    
    
    public function toArray()
    {
        return array(
            self::ON_CODE  => Mage::helper('olegnax_osc')->__(self::ON_LABEL),
            self::OFF_CODE => Mage::helper('olegnax_osc')->__(self::OFF_LABEL),
        );
    }
}
?>