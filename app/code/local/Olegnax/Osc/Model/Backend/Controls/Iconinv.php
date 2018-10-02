<?php 
class Olegnax_Osc_Model_Backend_Controls_Iconinv
{    
    public function toOptionArray()
    {
        return array(array('value'=>0, 'label'=>Mage::helper('olegnax_osc')->__('Black')), array('value'=>1, 'label'=>Mage::helper('olegnax_osc')->__('White')));
    }

}