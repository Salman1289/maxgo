<?php 
class Olegnax_Osc_Block_Form_Review_Terms extends Mage_Checkout_Block_Agreements
{
    public function showTerms()
    {
        if (count($this->getAgreements()) === 0) { return false; }
        return true;
    }
}
?>