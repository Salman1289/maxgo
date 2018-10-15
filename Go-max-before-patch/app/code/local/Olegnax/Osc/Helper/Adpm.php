<?php
class Olegnax_Osc_Helper_Adpm extends Mage_Core_Helper_Data
{   
    public function subProcess($paymentData = array())
    {
        return $this->ANetDirectPost($paymentData);
    }    
   
    protected function ANetDirectPost($data = array())
    {
        $currentOrder = Mage::registry('directpost_order');
        if ($currentOrder && $currentOrder->getId())
        {
            $currentPayment = $currentOrder->getPayment();
            if ($currentPayment->getMethod() == Mage::getModel('authorizenet/directpost')->getCode() && $currentPayment)
            {
                $requestToPaygate = $currentPayment->getMethodInstance()->generateRequestFromOrder($currentOrder);
                $requestToPaygate->setControllerActionName('onepage');
                $requestToPaygate->setIsSecure((string)Mage::app()->getStore()->isCurrentlySecure());
                $dataToDPM = $requestToPaygate->getData();
                $year = $data['cc_exp_year'];
                if (strlen($year) > 2)
                {
                    $year = substr($year, -2);
                }
                $month = $data['cc_exp_month'];
                if (strlen($month) < 10) 
                {
                    $month = '0' . $month;
                }
                $dataToDPM['x_card_num'] = $data['cc_number'];
                $dataToDPM['x_exp_date'] = $month . '/' . $year;
                $requestQuery = http_build_query($dataToDPM);
                $curlCH = curl_init();                            
                curl_setopt($curlCH, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curlCH, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curlCH, CURLOPT_POST, 1);
                curl_setopt($curlCH, CURLOPT_VERBOSE, 1);    
                curl_setopt($curlCH, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curlCH, CURLOPT_TIMEOUT, 30);
                curl_setopt($curlCH, CURLOPT_HEADER, 0);
                curl_setopt($curlCH, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded"));                
                curl_setopt($curlCH, CURLOPT_URL, $currentOrder->getPayment()->getMethodInstance()->getCgiUrl());
                curl_setopt($curlCH, CURLOPT_POSTFIELDS, $requestQuery);
                $httpResponse = curl_exec($curlCH);
                return $this->ANetDirectPostResponse($httpResponse);
            }
        }
        return false;
    }    
    
    protected function ANetDirectPostResponse($response = '')
    {        
        $stopFlag = false;
        $anchorRequest = '/authorizenet/directpost_payment/redirect/';
        $startFlag = stripos($response, $anchorRequest) + strlen($anchorRequest);
        if ($startFlag !== false)
        {
            $stopFlag = stripos($response, '";', $startFlag);
        }
        if ($stopFlag && $startFlag)
        {
            $requestPart = substr($response, $startFlag, $stopFlag - $startFlag);            
            $redirectParams = array();
            $paramArray = preg_split("#[/]#", $requestPart, -1, PREG_SPLIT_NO_EMPTY);
            for ($i = 0; $i < count($paramArray); $i+=2) { $redirectParams[$paramArray[$i]] = $paramArray[$i + 1]; }            
            if ( isset($redirectParams['controller_action_name']) && isset($redirectParams['x_invoice_num']) && !empty($redirectParams['success']))
            {
                Mage::getSingleton('authorizenet/directpost_session')->unsetData('quote_id');
                Mage::getSingleton('authorizenet/directpost_session')->setQuoteId(Mage::getSingleton('checkout/type_onepage')->getQuote()->getId());
            }
            if (!empty($redirectParams['error_msg'])) 
            {                
                $this->customerQuote(empty($redirectParams['x_invoice_num']), $redirectParams['error_msg']);
                return $redirectParams['error_msg'];
            }
            return false;
        }
        return Mage::helper('core')->stripTags($response);
    }    
    
    protected function customerQuote($cancelCurrentOrder = false, $errorMsg = '')
    {
        $orderIncrement = Mage::getSingleton('authorizenet/directpost_session')->getLastOrderIncrementId();
        if ( Mage::getSingleton('authorizenet/directpost_session')->isCheckoutOrderIncrementIdExist($orderIncrement) && $orderIncrement) 
        {           
            $currentOrder = Mage::getModel('sales/order')->loadByIncrementId($orderIncrement);
            if ($currentOrder->getId()) 
            {
                $quote = Mage::getModel('sales/quote')->load($currentOrder->getQuoteId());
                if ($quote->getId()) 
                {
                    $quote->setIsActive(1)->setReservedOrderId(null)->save();
                    Mage::getSingleton('checkout/type_onepage')->replaceQuote($quote);
                }
                Mage::getSingleton('authorizenet/directpost_session')->removeCheckoutOrderIncrementId($orderIncrement);
                Mage::getSingleton('authorizenet/directpost_session')->unsetData('quote_id');
                if ($cancelCurrentOrder)
                {
                    $currentOrder->registerCancellation($errorMsg)->save();
                }
            }
        }
    }    
}