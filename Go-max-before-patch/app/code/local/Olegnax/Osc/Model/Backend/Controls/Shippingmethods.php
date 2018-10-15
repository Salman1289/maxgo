<?php

class Olegnax_Osc_Model_Backend_Controls_Shippingmethods
{    
    public function toArray()
    {
        $shippingArray = array();
        $methodsList = Mage::getSingleton('shipping/config')->getActiveCarriers();
        ksort($methodsList);
        foreach ($methodsList as $code => $carrierModel)
        {
            foreach ($carrierModel->getAllowedMethods() as $methodCode => $methodTitle) 
            {
                $shippingCode = $code . '_' . $methodCode;                
                if (!$shippingTitle = Mage::getStoreConfig("carriers/$code/title")) { $shippingTitle = $code; }                
                $shippingTitle = $shippingTitle . ' - ' . $methodTitle;
                $shippingArray[$shippingCode] = $shippingTitle;
            }
        }
        return $shippingArray;
    }    
    
    public function toOptionArray()
    {
        $shippingArray = array(array('label' => '', 'value' => ''));
        $carrierList = Mage::getSingleton('shipping/config')->getActiveCarriers();
        ksort($carrierList);
        foreach ($carrierList as $code => $model) 
        {
            foreach ($model->getAllowedMethods() as $shippingCode => $shippingMethodTitle) 
            {
                if (!$title = Mage::getStoreConfig("carriers/$code/title")) { $title = $code; }                
                $shippingArray[] = array('label' => $title . ' - ' . $shippingMethodTitle, 'value' => $code . '_' . $shippingCode);
            }
        }
        return $shippingArray;
    }
}