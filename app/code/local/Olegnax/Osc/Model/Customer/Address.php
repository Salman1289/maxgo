<?php
class Olegnax_Osc_Model_Customer_Address extends Mage_Customer_Model_Address 
{
    protected function _basicCheck()
    {
        return Mage::helper('olegnax_osc/customeraddress')->checking($this);
    }
}
?>
