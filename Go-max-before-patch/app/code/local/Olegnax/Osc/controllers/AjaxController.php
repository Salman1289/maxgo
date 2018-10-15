<?php
class Olegnax_Osc_AjaxController extends Mage_Checkout_Controller_Action
{    
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();
        $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($sessionQuote->getIsMultiShipping()) 
        {
            $sessionQuote->setIsMultiShipping(false);
            $sessionQuote->removeAllAddresses();
        }
        return $this;
    }        
   
    public function logInAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionData = Mage::getSingleton('customer/session');
        $sessionResult = array('messages' => array(), 'success' => true);
        if (!$sessionData->isLoggedIn())
        {
            $userLogin = $this->getRequest()->getPost('login');
            if (!empty($userLogin['password']) && !empty($userLogin['username']))
            {
                try
                {
                    $sessionData->login($userLogin['username'], $userLogin['password']);
                }
                catch (Mage_Core_Exception $e) 
                {
                    switch ($e->getCode()) 
                    {
                        default:
                            $errorMessage = $e->getMessage();
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $errorMessage = $e->getMessage();
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $confirm = Mage::helper('customer')->getEmailConfirmationUrl($userLogin['username']);
                            $errorMessage = $this->__('Account is not confirmed. <a href="%s">Click here</a> to send confirmation email again.', $confirm);
                            break;
                    }
                    $sessionResult['messages'][] = $errorMessage;
                    $sessionResult['success'] = false;              
                    $sessionData->setUsername($userLogin['username']);
                } 
                catch (Exception $e)
                {                    
                    $sessionResult['messages'][] = $this->__("Undefined error.");
                    $sessionResult['success'] = false;                    
                }
            } else {                
                $sessionResult['messages'][] = $this->__('Please fill login and password fields.');
                $sessionResult['success'] = false;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function forgotPassAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages' => array(), 'success' => true);
        $sessionData = Mage::getSingleton('customer/session');
        if ($customerEmail = (string) $this->getRequest()->getPost('email'))
        {
            $validation = Zend_Validate::is($customerEmail, 'EmailAddress');
            if ($validation)
            {                
                $currentCustomer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customerEmail);
                if ($currentCustomer->getId())
                {
                    try
                    {
                        Mage::helper('olegnax_osc')->sendPassword($currentCustomer);
                    }
                    catch (Exception $e)
                    {                        
                        $sessionResult['messages'][] = $e->getMessage();
                        $sessionResult['success'] = false;
                    }
                }
            } 
            else
            {                
                $sessionResult['success'] = false;
                $sessionData->setForgottenEmail($customerEmail);
                $sessionResult['messages'][] = $this->__('Incorrect email address.');
            }
        } 
        else 
        {
            $sessionResult['messages'][] = $this->__('Please enter email.');
            $sessionResult['success'] = false;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }
    
    public function saveFormValuesAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('success'     => true, 'messages'    => array());
        if ($this->getRequest()->isPost())
        {
            $filledData = $this->getRequest()->getPost();            
            $currentData = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
            if (!is_array($currentData = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values'))) { $currentData = array(); }
            Mage::getSingleton('checkout/session')->setData('olegnax_osc_form_values', array_merge($currentData, $filledData));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function addressSaveAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages'=> array(), 'success'=>true, 'blocks' => array(), 'grand_total' => "");
        if ($this->getRequest()->isPost()) 
        {
            $billingData = $this->getRequest()->getPost('billing', array());
            $addressId = $this->getRequest()->getPost('billing_address_id', false);
            if (isset($billingData['email'])) 
            {
                $billingData['email'] = trim($billingData['email']);
            }
            $billingResult = Mage::helper('olegnax_osc/customeraddress')->billingSave($billingData, $addressId);
            $usingCase = 0;
            if(isset($billingData['use_for_shipping'])) { $usingCase = (int) $billingData['use_for_shipping']; }
            if ($usingCase === 0) 
            {                
                $addressId = $this->getRequest()->getPost('shipping_address_id', false);
                $billingData = $this->getRequest()->getPost('shipping', array());
                $shippingResult = Mage::helper('olegnax_osc/customeraddress')->shippingSave($billingData, $addressId);
            }
            if (isset($shippingResult)) 
            {
                $saveResult = array_merge($billingResult, $shippingResult);
            }
            else 
            {
                $saveResult = $billingResult;
            }
            if (isset($saveResult['error']))
            {
                $sessionResult['success'] = false;
                if (!is_array($saveResult['message']))
                {
                    $sessionResult['messages'][] = $saveResult['message'];
                } 
                else 
                {                    
                    $sessionResult['messages'] = array_merge($sessionResult['messages'], $saveResult['message']);
                }
            }
            $rates = Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->collectTotals()->collectShippingRates()->getAllShippingRates();            
            if (count($rates) == 1)
            {
                $shippingMethod = $rates[0]->getCode();
                Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
            }
            Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
            $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
            $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
        }
        else
        {
            $sessionResult['messages'][] = $this->__('Please specify billing address information.');
            $sessionResult['success'] = false;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    } 
    
    public function shippingSaveAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('grand_total'=>"", 'messages'=>array(), 'blocks'=>array(), 'success'=>true);
        if ($this->getRequest()->isPost())
        {
            $shippingData = $this->getRequest()->getPost('shipping_method', '');
            $fillData = Mage::getSingleton('checkout/type_onepage')->saveShippingMethod($shippingData);
            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>Mage::getSingleton('checkout/type_onepage')->getQuote()));
            if (isset($fillData['error']))
            {
                $sessionResult['messages'][] = $fillData['message'];
                $sessionResult['success'] = false;
            }
            Mage::getSingleton('checkout/type_onepage')->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
            $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
            $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
        } 
        else
        {
            $sessionResult['messages'][] = $this->__('Please specify shipping method.');
            $sessionResult['success'] = false;            
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function setPrintedCardForEnterpriseAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('printed_card_applied' => false, 'messages'=> array(),  'blocks' => array(), 'success'=> true, 'grand_total' => "");
        if (Mage::getSingleton('checkout/type_onepage')->getQuote()->getItemsCount()) 
        { 
            try 
            {
                $qt = Mage::getSingleton('checkout/type_onepage')->getQuote();
                $enfoldingInfo = array();
                $enfoldingInfo['gw_add_card'] = (bool)$this->getRequest()->getParam('add_printed_card');
                if ($qt->getShippingAddress()) { $quote->getShippingAddress()->addData($enfoldingInfo); }
                $qt->addData($enfoldingInfo);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->setTotalsCollectedFlag(false);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
                $sessionResult['printed_card_applied'] = $enfoldingInfo['gw_add_card'];
            }
            catch (Mage_Core_Exception $e)
            {
                $sessionResult['messages'][] = $e->getMessage();
                $sessionResult['success'] = false;                
            }
            catch (Exception $e)
            {
                Mage::logException($e);
                $sessionResult['messages'][] = $this->__("Printed Card can't  be added.");
                $sessionResult['success'] = false;                
            }            
        } 
        else 
        {
            $sessionResult['success'] = false;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function paymentSaveAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages'=>array(), 'blocks'=>array(), 'success'=>true, 'grand_total' =>"");
        try
        {            
            if ($this->getRequest()->isPost()) 
            {
                $paymentData = $this->getRequest()->getPost('payment', array());
                $currentSession = Mage::getSingleton('checkout/session');
                $paymentData['use_points'] = $currentSession->getData('use_points');
                $paymentData['points_amount'] = $currentSession->getData('points_amount');
                $saveData = Mage::getSingleton('checkout/type_onepage')->savePayment($paymentData);
                if (isset($saveData['error']))
                {                    
                    $sessionResult['messages'][] = $saveResult['message'];
                    $sessionResult['success'] = false;
                }
                Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
            } 
            else
            {
                    $sessionResult['success'] = false;
                    $sessionResult['messages'][] = $this->__('Please specify payment method.');
            }
        } 
        catch (Exception $e) 
        {
            Mage::logException($e);
            $sessionResult['error'][] = $this->__("Can't set Payment Method.");
            $sessionResult['success'] = false;           
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function confirmCouponAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('blocks'=> array(),'grand_total'=>"", 'coupon_applied'=>false, 'messages'=> array(), 'success'=> true);
        if (!Mage::getSingleton('checkout/type_onepage')->getQuote()->getItemsCount()) 
        {
            $sessionResult['success'] = false;			
        } 
        else 
        {
            $newCouponCode = (string) $this->getRequest()->getParam('coupon_code');
            $oldCouponCode = Mage::getSingleton('checkout/type_onepage')->getQuote()->getCouponCode();
            if (strlen($newCouponCode) || strlen($oldCouponCode))
            {                
                try 
                {                    
                    Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    if(!strlen($newCouponCode))
                    {
                        $newCouponCode = '';
                    }
                    Mage::getSingleton('checkout/type_onepage')->getQuote()->setCouponCode($newCouponCode)->collectTotals()->save();
                    if ($newCouponCode == Mage::getSingleton('checkout/type_onepage')->getQuote()->getCouponCode()) 
                    {                        
                        Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                        Mage::getSingleton('checkout/type_onepage')->getQuote()->setTotalsCollectedFlag(false);
                        Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();                        
                        Mage::getSingleton('checkout/session')->getMessages(true);
                        if (!strlen($newCouponCode)) 
                        {                             
                            $sessionResult['messages'][] = $this->__('Coupon code was canceled.');
                            $sessionResult['coupon_applied'] = false;
                        } 
                        else 
                        {                            
                            $sessionResult['messages'][] = $this->__('Coupon code was applied.');
                            $sessionResult['coupon_applied'] = true;
                        }
                    } 
                    else 
                    {
                        $sessionResult['messages'][] = $this->__('Coupon code is incorrect.');
                        $sessionResult['success'] = false;                        
                    }
                    $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                    $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
                } 
                catch (Mage_Core_Exception $e) 
                {
                    $sessionResult['messages'][] = $e->getMessage();
                    $sessionResult['success'] = false;                    
                } 
                catch (Exception $e) 
                {
                    $sessionResult['messages'][] = $this->__('Cannot apply the coupon code.');
                    $sessionResult['success'] = false;
                    Mage::logException($e);
                }
            } else 
            {
                $sessionResult['success'] = false;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }       
    
    public function confirmStorecreditForEnterpriseAction()
    {
        
        
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('grand_total'=>"", 'messages'=>array(), 'success'=>true, 'blocks'=>array());
        if (Mage::getSingleton('checkout/type_onepage')->getQuote()->getItemsCount())
        {
            try 
            {
                $qt = Mage::getSingleton('checkout/type_onepage')->getQuote();
                $store = Mage::app()->getStore($qt->getStoreId());
                if (!$qt->getCustomerId() ||  $qt->getBaseGrandTotal() + $qt->getBaseCustomerBalanceAmountUsed() <= 0 || !$qt)
                {
                    $sessionResult['success'] = false;
                }

                $qt->setUseCustomerBalance((bool)$this->getRequest()->getParam('use_customer_balance'));
                $castomBalance = $qt->getUseCustomerBalance();
                if ($castomBalance)
                {
                    $balance = Mage::getModel('enterprise_customerbalance/balance')->setCustomerId($qt->getCustomerId())->setWebsiteId($store->getWebsiteId())->loadByCustomer();
                    if (!$balance) 
                    {
                        $sessionResult['messages'][] = $this->__('Store Credit payment is not being used in your shopping cart.');
                        $qt->setUseCustomerBalance(false);
                    }
                    else
                    {                        
                        $qt->setCustomerBalanceInstance($balance);
                        if (!$qt->getPayment()->getMethod())
                        {
                            $qt->getPayment()->setMethod('free');
                        }
                        $sessionResult['messages'][] = $this->__('Store credit was applied.');
                    }
                }
                else 
                {
                    $sessionResult['messages'][] = $this->__('The store credit payment has been removed from the order.');
                    $qt->setUseCustomerBalance(false);                    
                }
                Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->setTotalsCollectedFlag(false);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
            }
            catch (Mage_Core_Exception $e) 
            {
                $sessionResult['messages'][] = $e->getMessage();
                $sessionResult['success'] = false;                
            } 
            catch (Exception $e) 
            {
                $sessionResult['messages'][] = $this->__('Cannot apply the Store Credit.');
                $sessionResult['success'] = false;                
                Mage::logException($e);
            }            
        }
        else 
        {
            $sessionResult['success'] = false;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function confirmPointsForEnterpriseAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages' => array(), 'success' => true, 'grand_total' => "", 'blocks' => array());
        if (Mage::getSingleton('checkout/type_onepage')->getQuote()->getItemsCount())
        {            
            try 
            {
                $qt = Mage::getSingleton('checkout/type_onepage')->getQuote();
                if (!$qt->getCustomerId() || $qt->getBaseGrandTotal() + $qt->getBaseRewardCurrencyAmount() <= 0 || !$qt) 
                {
                    $sessionResult['success'] = false;
                }
                $qt->setUseRewardPoints((bool)$this->getRequest()->getParam('use_reward_points'));
                if ($qt->getUseRewardPoints())
                {                    
                    $tribute = Mage::getModel('enterprise_reward/reward')->setCustomer($qt->getCustomer())->setWebsiteId($qt->getStore()->getWebsiteId())->loadByCustomer();
                    $minPointsBalance = (int)Mage::getStoreConfig(Enterprise_Reward_Model_Reward::XML_PATH_MIN_POINTS_BALANCE, $qt->getStoreId());
                    if ($tribute->getId() && $tribute->getPointsBalance() >= $minPointsBalance) 
                    {
                        $qt->setRewardInstance($tribute);
                        if (!$qt->getPayment()->getMethod()) 
                        {
                            $qt->getPayment()->setMethod('free');
                        }
                        $sessionResult['messages'][] = $this->__('Reward points was applied.');
                    } 
                    else 
                    {                        
                        $sessionResult['messages'][] = $this->__('Reward points will not be used in this order.');
                        $qt->setUseRewardPoints(false);
                    }
                } else {                    
                    $sessionResult['messages'][] = $this->__('The reward points have been removed from the order.');
                    $qt->setUseRewardPoints(false);
                }
                Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->setTotalsCollectedFlag(false);
                Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
            } 
            catch (Mage_Core_Exception $e)
            {
                $sessionResult['success'] = false;
                $sessionResult['messages'][] = $e->getMessage();
            } 
            catch (Exception $e) 
            {
                $sessionResult['success'] = false;
                $sessionResult['messages'][] = $this->__('Cannot apply the %s.', Mage::helper('olegnax_osc')->pointsUnit());
                Mage::logException($e);
            }
        } 
        else
        {
            $sessionResult['success'] = false;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }    
    
    public function confirmOrderAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages' => array(), 'success'  => true);
        try
        {            
            if ($this->getRequest()->isPost())
            {
                $billing = $this->getRequest()->getPost('billing', array());                
                if (!Mage::getSingleton('checkout/type_onepage')->getCustomerSession()->isLoggedIn()) 
                {
                    if (!isset($billing['create_account']))
                    {
                        Mage::getSingleton('checkout/type_onepage')->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
                    } 
                    else 
                    {
                        Mage::getSingleton('checkout/type_onepage')->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
                    }
                }
                if (Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER == Mage::getSingleton('checkout/type_onepage')->getQuote()->getCheckoutMethod() && !Mage::getSingleton('checkout/type_onepage')->getQuote()->getCustomerId())
                {
                    $client = Mage::getModel('customer/customer');
                    $client->setWebsiteId(Mage::app()->getWebsite()->getId());
                    $client->loadByEmail($billing['email']);
                    $client->getId();
                    if ($client->getId())
                    {                        
                        $sessionResult['messages'][] = $this->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
                        $sessionResult['success']    = false;
                    }
                }
                if ($sessionResult['success'])
                {                    
                    if (isset($billing['email']))
                    {
                        $billing['email'] = trim($billing['email']);
                    }
                    $addressId = $this->getRequest()->getPost('billing_address_id', false);
                    $billingResult = Mage::getSingleton('checkout/type_onepage')->saveBilling($billing, $addressId);                    
                    if (!isset($billing['use_for_shipping']))
                    {
                        $addressId = $this->getRequest()->getPost('shipping_address_id', false);
                        $shippingResult = Mage::getSingleton('checkout/type_onepage')->saveShipping($this->getRequest()->getPost('shipping', array()), $addressId);
                    }                    
                    if (isset($shippingResult)) { $saveResult = array_merge($billingResult, $shippingResult); }
                    else { $saveResult = $billingResult; }
                    Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(), 'quote'   => Mage::getSingleton('checkout/type_onepage')->getQuote()));
                    if (isset($saveResult['error']))
                    {
                        $sessionResult['success'] = false;
                        if (!is_array($saveResult['message'])) { $saveResult['message'] = array($saveResult['message']); }
                        $sessionResult['messages'] = array_merge($sessionResult['messages'], $saveResult['message']);
                    }
                    else 
                    {                        
                        if ($diff = array_diff(Mage::helper('checkout')->getRequiredAgreementIds(), array_keys($this->getRequest()->getPost('olegnax_osc_agreement', array())))) 
                        {                                
                            $sessionResult['messages'][] = $this->__('Please agree to all the terms and conditions before placing the order.');
                            $sessionResult['success'] = false;
                        }
                        else
                        {
                            $data = $this->getRequest()->getPost('payment', false);
                            if ($data)
                            {
                                Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment()->importData($data);
                            }
                            Mage::getSingleton('checkout/session')->setData('points_amount', Mage::getSingleton('checkout/session')->getData('points_amount'));
                            Mage::getSingleton('checkout/session')->setData('use_points', Mage::getSingleton('checkout/session')->getData('use_points'));                                
                            $data = array('comments' => $this->getRequest()->getPost('comments', false), 'is_subscribed' => $this->getRequest()->getPost('is_subscribed', false), 'billing' => $this->getRequest()->getPost('billing', array()), 'segments_select' => $this->getRequest()->getPost('segments_select', array()));
                            Mage::getSingleton('checkout/session')->setData('olegnax_osc_order_data', $data);                                
                            if (@class_exists('Mage_Authorizenet_Model_Directpost_Session'))
                            {
                                Mage::getSingleton('authorizenet/directpost_session')->setQuoteId(Mage::getSingleton('checkout/type_onepage')->getQuote()->getId());
                            }
                            $paymentMethod = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment()->getMethod();
                            if (Mage::helper('olegnax_osc')->ebizmartsEnabled($paymentMethod))
                            {
                                $redirect = $this->sagePaySuite();
                            }
                            else 
                            {                                    
                                $redirect = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment()->getCheckoutRedirectUrl();
                                if (!$redirect) 
                                {
                                    Mage::getSingleton('checkout/type_onepage')->saveOrder();                                        
                                    if ($paymentMethod == 'authorizenet_directpost') 
                                    {                                            
                                        $dpmError = Mage::helper('olegnax_osc/adpm')->subProcess($this->getRequest()->getPost('payment', false));
                                        if ($dpmError) 
                                        {
                                            throw new Exception($dpmError);
                                        }
                                    }
                                    $redirect = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getRedirectUrl();
                                }
                            }
                        }
                    }
                }
            } 
            else 
            {
                $sessionResult['success'] = false;
            }
        }
        catch (Exception $e) 
        {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail(Mage::getSingleton('checkout/type_onepage')->getQuote(), $e->getMessage());
            $sessionResult['messages'][] = $this->__('There was some errors processing your order. Please contact us.');
            $sessionResult['success']    = false;
            $sessionResult['messages'][] = $e->getMessage();
        }        
        if ($sessionResult['success'])
        {
            Mage::getSingleton('checkout/type_onepage')->getQuote()->save();
            if (isset($redirect)) { $sessionResult['redirect'] = $redirect; }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function addToWishlistAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages' => array(), 'success' => true);
        $response = clone $this->getResponse();        
        $wishlistController = $this->addController('Mage_Wishlist', 'index', $this->getRequest(), $response);         
        if (method_exists($wishlistController, 'addAction') && !is_null($wishlistController))
        {
            $wishlistController->addAction();            
            $successMessages = array_merge(Mage::getSingleton('wishlist/session')->getMessages(true)->getItemsByType(Mage_Core_Model_Message::SUCCESS), Mage::getSingleton('customer/session')->getMessages(true)->getItemsByType(Mage_Core_Model_Message::SUCCESS));
            if (count($successMessages) === 0) 
            {
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                $sessionResult['success'] = false;                
                if (!is_null($product->getId()))
                {                    
                    $sessionResult['messages'][] = $this->__('Product "%1$s" has not been added. Please add it <a href="%2$s">from product page</a>', $product->getName(), $product->getUrlModel()->getUrl($product, array()));
                }
            } 
            else
            {
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();                
                if (is_null($product->getId()))
                {
                    $sessionResult['messages'][] = $this->__('Product was successfully added to wishlist');
                } 
                else
                {                    
                    $sessionResult['messages'][] = $this->__('Product "%1$s" was successfully added to wishlist', $product->getName());
                }
            }
        } 
        else
        {
            $sessionResult['messages'][] = $this->__("Undefined error");
            $sessionResult['success'] = false;            
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
   
    public function addToCompareAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages' => array(), 'success' => true);
        $response = clone $this->getResponse();
        $compareController = $this->addController('Mage_Catalog', 'product_compare', $this->getRequest(), $response);
        if (!is_null($compareController) && method_exists($compareController, 'addAction'))
        {
            $compareController->addAction();            
            $successMessages = Mage::getSingleton('catalog/session')->getMessages(true)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
            if (count($successMessages) === 0) 
            {                
                $sessionResult['success'] = false;
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                if (!is_null($product->getId())) { $sessionResult['messages'][] = $this->__('Product "%1$s" has not been added. Please add it <a href="%2$s">from product page</a>', $product->getName(), $product->getUrlModel()->getUrl($product, array())); }
            } 
            else 
            {
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product', 0));
                if (!is_null($product->getId())) { $sessionResult['messages'][] = $this->__('Product "%1$s" was successfully added to compare list', $product->getName()); }
                else { $sessionResult['messages'][] = $this->__('Product was successfully added to compare list'); }
            }
        } 
        else 
        {
            $sessionResult['messages'][] = $this->__("Undefined error");
            $sessionResult['success'] = false;            
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }    
    
    public function confirmGiftcardForEnterpriseAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages'=>array(), 'success'=>false, 'grand_total'=>"", 'blocks'=>array());
        $cardCode = (string) $this->getRequest()->getParam('enterprise_giftcard_code');
        if (!(strlen($cardCode) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH) || isset($cardCode))
        {
            try 
            {
                $sessionResult['success'] = true;
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->loadByCode($cardCode)->addToCart();                
                $sessionResult['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($cardCode));
                Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
            } 
            catch (Mage_Core_Exception $e)
            {
                $sessionResult['messages'][] = $e->getMessage();
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $cardCode));                
            }
            catch (Exception $e)
            {
                $sessionResult['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Cannot apply gift card.');
            }
        } 
        else 
        {
            $sessionResult['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function dellGiftcardForEnterpriseAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages'=>array(), 'grand_total' =>"",  'blocks'=>array(), 'success'=>false);
        $cardCode = (string) $this->getRequest()->getParam('enterprise_giftcard_code');
        if (isset($cardCode) || !(strlen($cardCode) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH)) 
        {
            try 
            {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')->loadByCode($cardCode)->removeFromCart();
                $sessionResult['success'] = true;
                $sessionResult['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Gift Card "%s" was removed.', Mage::helper('core')->escapeHtml($cardCode));
                Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
            } 
            catch (Mage_Core_Exception $e) 
            {
                $sessionResult['messages'][] = $e->getMessage();
            } 
            catch (Exception $e) 
            {
                $sessionResult['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Cannot remove gift card.');
            }
        } 
        else
        {
            $sessionResult['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }        
    
    public function cartUpdateAction()
    {
        if ($this->isAjaxLost()) { return; }
        $sessionResult = array('messages' => array(), 'grand_total' => "", 'success'=>true, 'blocks' => array());
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getParam('cart');
            if (is_array($data))
            {
                $strainer = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
                foreach ($data as $index => $item)
                {                    
                    if (isset($item['qty'])) 
                    {
                        $data[$index]['qty'] = $strainer->filter(trim($item['qty']));
                    }
                }
                $cart = Mage::getSingleton('checkout/cart');
                if ($cart->getQuote()->getCustomerId() && !$cart->getCustomerSession()->getCustomer()->getId()) { $cart->getQuote()->setCustomerId(null); }
                $data = $cart->suggestItemsQty($data);
                $cart->updateItems($data)->save();
            }
            if (!Mage::getSingleton('checkout/cart')->getQuote()->getHasError() && Mage::getSingleton('checkout/cart')->getSummaryQty() > 0)
            {
                $sessionResult['blocks'] = Mage::getSingleton('olegnax_osc/updater')->getBlocks();
                $sessionResult['grand_total'] = Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote());
            }
            else
            {
                $sessionResult['redirect'] = Mage::helper('checkout/cart')->getCartUrl();
            }                
        } 
        else 
        {
            $sessionResult['messages'][] = $this->__('Please specify items.');
            $sessionResult['success'] = false;            
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }    
    
    protected function isAjaxLost()
    {
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true) || (!Mage::getSingleton('checkout/type_onepage')->getQuote()->hasItems() || Mage::getSingleton('checkout/type_onepage')->getQuote()->getHasError() || Mage::getSingleton('checkout/type_onepage')->getQuote()->getIsMultiShipping()))
        {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired')->setHeader('Required login', 'true')->sendResponse();
            return true;
        }
        return false;
    }    
    
    private function addController($moduleName, $controllerName, $request, $response)
    {
        $router = Mage::app()->getFrontController()->getRouter('standard');
        $controllerFile = $router->getControllerFileName($moduleName, $controllerName);
        if (!$router->validateControllerFileName($controllerFile)) { return null; }
        $controllerClass = $router->getControllerClassName($moduleName, $controllerName);
        if (!$controllerClass) { return null; }
        if (!class_exists($controllerClass, false)) 
        {
            if (!file_exists($controllerFile)) { return null; }
            include $controllerFile;
            if (!class_exists($controllerClass, false)) { return null; }
        }        
        return Mage::getControllerInstance( $controllerClass, $request, $response);
    }
        
    protected function sagePaySuite()
    {
        switch (Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment()->getMethod()) {            
            case 'sagepayserver':
                $this->_forward('saveOrder', 'serverPayment', 'sgps', $this->getRequest()->getParams());
                break;            
            case 'sagepayform':
                $this->_forward('saveOrder', 'formPayment', 'sgps', $this->getRequest()->getParams());
                break;
            case 'sagepaydirectpro':
                $this->_forward('saveOrder', 'directPayment', 'sgps', $this->getRequest()->getParams());
                break;
            case 'sagepaypaypal':
                return Mage::getModel('core/url')->addSessionParam()->getUrl('sgps/paypalexpress/go', array('_secure' => true));
            default:
                return null;
        }
    }
    
    public function updateBlocksAfterACPAction()
    {
        if ($this->isAjaxLost) { return; }
        $sessionResult = array('success'     => true, 'messages'    => array(), 'blocks' => $this->getUpdater()->getBlocks(), 'can_shop' => !Mage::getSingleton('checkout/type_onepage')->getQuote()->isVirtual(), 'grand_total' => Mage::helper('olegnax_osc')->getUberTotal(Mage::getSingleton('checkout/type_onepage')->getQuote()));
        switch($this->getRequest()->getParam('action', 'add')) 
        {
            case 'add':
                $sessionResult['messages'][] = $this->__('Product was successfully added to the cart');
                break;
            case 'remove':
                $sessionResult['messages'][] = $this->__('Product was successfully remove from the cart');
                break;
            default:
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sessionResult));
    }
}
?>