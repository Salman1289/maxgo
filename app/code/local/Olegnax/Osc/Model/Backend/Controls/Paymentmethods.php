<?php

class Olegnax_Osc_Model_Backend_Controls_Paymentmethods
{
    public function toArray()
    {
        $methodsArray = array();
        $methodsList = Mage::getModel('payment/config')->getActiveMethods();
        ksort($methodsList);
        foreach ($methodsList as $code => $method)
        {
            $methodsArray[$code] = $method->getTitle();
        }
        return $methodsArray;
    }
    
    public function toOptionArray()
    {
        $paymentList = Mage::getModel('payment/config')->getActiveMethods();
        $methodsOption = array(array('label' => '', 'value' => ''));
        ksort($paymentList);
        foreach ($paymentList as $code => $method)
        {
            if ($code == 'googlecheckout') { continue; }
            $methodsOption[] = array('label' => $method->getTitle(), 'value' => $code);
        }
        return $methodsOption;
    }
}