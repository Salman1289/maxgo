<?php 
class Olegnax_Osc_Model_Backend_Controls_Iconpos
{    
    public function toOptionArray()
    {
        return array(array('value'=>0, 'label'=>Mage::helper('olegnax_osc')->__('Left')), array('value'=>1, 'label'=>Mage::helper('olegnax_osc')->__('Right')));
    }

}