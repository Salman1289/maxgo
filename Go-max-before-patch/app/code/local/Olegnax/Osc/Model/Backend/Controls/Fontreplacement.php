<?php 
class Olegnax_Osc_Model_Backend_Controls_Fontreplacement
{    
    public function toOptionArray()
    {
        return array(array('value'=>0, 'label'=>Mage::helper('olegnax_osc')->__('Disable')), array('value'=>2, 'label'=>Mage::helper('olegnax_osc')->__('Google Fonts')));
    }

}