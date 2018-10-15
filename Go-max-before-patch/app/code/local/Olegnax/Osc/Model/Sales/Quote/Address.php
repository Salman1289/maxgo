<?php
class Olegnax_Osc_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address 
{    
    protected function _basicCheck()
    {
        return Mage::helper('olegnax_osc/customeraddress')->checking($this);
    }
}
?>
