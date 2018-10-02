<?php 
class Olegnax_Osc_Block_Form_Review_Comments extends Mage_Checkout_Block_Onepage_Abstract
{    
    public function showComments()
    {
        if (!Mage::helper('olegnax_osc/config')->isCommments())
        {
            return false;
        }
        return true;
    }        
    
    public function saveUrl()
    {
        return Mage::getUrl('olegnax_osc/ajax/saveFormValues');
    }
    
    public function getCustomerComments()
    {
        $commentsData = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
        if (isset($commentsData['comments'])) { return $commentsData['comments']; }
        return '';
    }
}
?>