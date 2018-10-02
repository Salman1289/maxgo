<?php
class Olegnax_Osc_Block_Form_Review_Enterprise_Points extends Mage_Checkout_Block_Onepage_Abstract
{
    public function pointsAvailable()
    {
        return Mage::helper('olegnax_osc')->pointsAvailable();
    } 
    
    public function getSummary()
    {
        return Mage::helper('olegnax_osc')->customerSummary();
    }    
    
    public function getApplyUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/confirmPointsForEnterprise', array('_secure' => true));
    }
    
    public function pointsUnitName()
    {
        return Mage::helper('olegnax_osc')->pointsUnit();
    }    
    
    public function pointsToMoney()
    {
        return Mage::helper('olegnax_osc')->pointsToMoney();
    }
    
    public function getMaxAvailablePointsAmount()
    {
        return min($this->getSummary()->getPoints(), $this->getNeededPoints(), $this->getLimitedPoints());
    }
    
    public function useRewardPoints()
    {
        return Mage::helper('olegnax_osc')->doRewardForPoints();
    }        
    
    public function canShowPointsForEnterprise()
    {
        if (Mage::helper('olegnax_osc')->pointsEnabled()) { return true; }
        else { return false; }
    }
}