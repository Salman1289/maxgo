<?php
class Olegnax_Osc_Model_Observer
{
	const GEOLITE_DOWNLOAD_URL = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz';
	
    protected $_skinModel = null;    
    
    public function setSkinModel(Mage_Core_Model_Config_Data $model) { $this->_skinModel = $model; }    
    
    public function getSkinModel() { return $this->_skinModel; }    
    
    public function addCompleteCC($observer)
    {
        if ( !Mage::helper('olegnax_osc/config')->enabledRedirectTo() || !Mage::helper('olegnax_osc/config')->isAllStuffEnabled()) { return; }        
        Mage::app()->getRequest()->setParam('return_url', Mage::getUrl('onestepcheckout/index', array('_secure'=>true)));
    }    
    
    public function predispatchCheckout($observer)
    {
        $controller = $observer->getControllerAction();
        if ($controller instanceof Mage_Checkout_OnepageController && $controller->getRequest()->getActionName() !== 'success' && $controller->getRequest()->getActionName() !== 'failure' && $controller->getRequest()->getActionName() !== 'saveOrder' && Mage::helper('olegnax_osc/config')->isAllStuffEnabled())
        {
            $controller->getResponse()->setRedirect(Mage::getUrl('onestepcheckout/index', array('_secure'=>true)));
            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }
    }    
    
    public function submitAll($observer)
    {
        $orderData = Mage::getSingleton('checkout/session')->getData('olegnax_osc_order_data');
        if (!is_array($orderData)) { $orderData = array(); }
		
		if (array_key_exists('comments', $orderData)) 
		{
			$idOflastOrder = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
			if ($idOflastOrder) 
			{ Mage::getModel('sales/order')->load($idOflastOrder)->addStatusHistoryComment(Mage::helper('olegnax_osc')->__('Comment by customer: %s', $orderData['comments']))->setIsVisibleOnFront(true)->save(); }
		}
        
        if (array_key_exists('is_subscribed', $orderData) && $orderData['is_subscribed']) 
        {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getId()) { $data = array('email'       => $customer->getEmail(), 'first_name'  => $customer->getFirstname(), 'last_name'   => $customer->getLastname(), 'customer_id' => $customer->getId()); } 
            else 
            {                
                $data = array('email'      => $orderData['billing']['email'], 'first_name' => $orderData['billing']['firstname'], 'last_name'  => $orderData['billing']['lastname']);
            }
            if (array_key_exists('segments_select', $orderData)) { $data['segments_codes'] = $orderData['segments_select']; }
            $data['store_id'] = Mage::app()->getStore()->getId();
            Mage::helper('olegnax_osc')->subscribeCustomer($data);
        }        
        Mage::getSingleton('checkout/session')->setData('olegnax_osc_form_values', array());
        Mage::getSingleton('checkout/session')->setData('olegnax_osc_order_data', array());
    }
   
    //DONE+
    public function postdispatchAjaxPlaceOrder($observer)
    {
        $paypal = Mage::getModel('paypal/observer');
        if (!method_exists($paypal, 'setResponseAfterSaveOrder')) { return $this; }
        $controller = $observer->getEvent()->getControllerAction();
        $result = Mage::helper('core')->jsonDecode( $controller->getResponse()->getBody(), Zend_Json::TYPE_ARRAY);
        if ($result['success']) 
        {
            $paypal->setResponseAfterSaveOrder($observer);
            $result = Mage::helper('core')->jsonDecode( $controller->getResponse()->getBody(), Zend_Json::TYPE_ARRAY );
            $result['is_hosted_pro'] = true;
            $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    public function createOnestepcheckoutAfter($observer)
    {
        if (Mage::app()->getRequest()->getControllerModule() !== 'Olegnax_Osc') { return $observer; }
        $block = $observer->getBlock();
        if ($block instanceof Mage_Authorizenet_Block_Directpost_Form) { $block->setTemplate('olegnax_osc/form/payment/authorizenet/directpost.phtml'); }
    }
    
    public function getUpdatesAfter($observer)
    {        
        $rootUpdate = $observer->getUpdates();
        $nodeListToRemove = array();
        foreach ($rootUpdate->children() as $updateKey => $updateNode) 
        {
            if ($updateNode->file) { if (strpos($updateKey, 'olegnax_osc') !== false) { if ($updateNode->getAttribute('module') && !Mage::helper('core')->isModuleOutputEnabled($updateNode->getAttribute('module'))) { $nodeListToRemove[] = $updateKey; }}}
        }
        foreach ($nodeListToRemove as $nodeKey) { unset($rootUpdate->$nodeKey); }
    }    
	
    public function configChanged($observer)
    {		
		if ($skinModel = $this->getSkinModel()){ $skinModel->checkAfterSave(); }
		$section = 'olegnax_appearance';
		$resetTriggerPath = $section . '/reset/trigger';
		$resetTrigger = Mage::getStoreConfig($resetTriggerPath);
		$configModel = new Mage_Core_Model_Config();		
		if($resetTrigger)
		{
			$configModel->saveConfig($resetTriggerPath, '0');
			$this->resetSettings($section);	
		}
    }
	
	public function mainConfigChanged()
	{
		$section = 'olegnax_osc';
		$resetTriggerPath = $section . '/reset/trigger';
		$downloadTriggerPath = $section . '/geoip/trigger';
		$resetTrigger = Mage::getStoreConfig($resetTriggerPath);
		$downloadTrigger = Mage::getStoreConfig($downloadTriggerPath);
		$configModel = new Mage_Core_Model_Config();		
		if($resetTrigger)
		{
			$configModel->saveConfig($resetTriggerPath, '0');
			$this->resetSettings($section);		
		}
		if($downloadTrigger)
		{
			$configModel->saveConfig($downloadTriggerPath, '0');
			$this->downloadGeoIP();
		}
	}
	
	protected function fileDownload($targetFIle)
    {
        $fileWrite = fopen($targetFIle, 'wb');
        if ($fileWrite) 
		{
			$downloadUrl = self::GEOLITE_DOWNLOAD_URL;
			$curlStr = curl_init(); 
			curl_setopt($curlStr, CURLOPT_FILE, $fileWrite);        
			curl_setopt($curlStr, CURLOPT_URL, $downloadUrl);
			curl_setopt($curlStr, CURLOPT_HEADER, 0); 
			curl_exec($curlStr); 
			$error = curl_error($curlStr); 
			$httpCode = curl_getinfo($curlStr, CURLINFO_HTTP_CODE);
			curl_close($curlStr);
			if ($error)
			{
				return 'Can not download file: ' . $error; 
			}
			$downloadUrl = parse_url($downloadUrl);
			if (in_array($downloadUrl['scheme'], array('http', 'https')) && $httpCode != 200)
			{
				return 'Can not download file: HTTP Status ' . $httpCode; 
			}
			if (in_array($downloadUrl['scheme'], array('ftp')) && $httpCode != 226) 
			{
				return 'Can not download file: FTP Error ' . $httpCode;
			}
			fclose($fileWrite);
			return;
		}
		else
		{
			return 'Can not open file for writing: ' . $targetFIle; 
		}
    }    
    
    protected function fileUnpack($source, $targetFIle)
    {
        $fileRead = gzopen($source, 'rb');
        if (!$fileRead) 
		{
			return 'Can not open file for reading: ' . $source; 
		}
        $fileWrite = fopen($targetFIle, 'wb');
        if (!$fileWrite) 
		{
			return 'Can not open file for writing: ' . $targetFIle;
		}
        while (!gzeof($fileRead)) { fwrite($fileWrite, gzread($fileRead, 64 * 1024)); }
        gzclose($fileRead);
        fclose($fileWrite);
    }    
   
    protected function downloadGeoIP()
    {
        $result = '';
        try 
        {
			$configModel = new Mage_Core_Model_Config();
            $dir = Mage::getBaseDir('var') . '/';
            $downloadFile = $dir . 'geoip.mmdb.gz';
            $result = $this->fileDownload($downloadFile);
			if($result)
			{
				Mage::getSingleton('core/session')->addError($result);
				return;
			}
            $unpackedTemp = $dir . 'geoip.mmdb.tmp';
            $result = $this->fileUnpack($downloadFile, $unpackedTemp);
			if($result)
			{
				Mage::getSingleton('core/session')->addError($result);
				return;
			}
            $targetFile = $dir . 'geoip.mmdb';
            $backupFile = $dir . 'geoip.mmdb.bak';
            @rename($targetFile, $backupFile);
            @rename($unpackedTemp, $targetFile);
            @unlink($downloadFile);
            @unlink($unpackedTemp);
			$configModel->saveConfig('olegnax_osc/geoip/path', $targetFile);
			Mage::getSingleton('core/session')->addSuccess('Database has been successfully saved.');
        } 
        catch (Mage_Core_Exception $e) 
        {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        catch (Exception $e) 
        {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError('Unknown Error');
        }
    }    
    
    protected function resetSettings($section)
    {        
        if (!empty($section))
        {
            try 
            {
                Mage::helper('olegnax_osc/config')->sectionReset($section);
                Mage::getSingleton('core/session')->addSuccess('Config was successfully reset to default.');
            } 
            catch (Mage_Core_Exception $e) 
            {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }            
        }            
    }    
    
    protected function regionHack(Mage_Core_Block_Abstract $block, Varien_Object $transport)
    {
        if (Mage::app()->getRequest()->getParam('section', '') !== 'olegnax_osc') { return; }
        $html = $transport->getHtml();
        $html = str_replace('required-entry', '', $html);
        $transport->setHtml($html);
    }    
    
    public function afterHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ( ! $block) { return; }
        $transport = $observer->getEvent()->getTransport();
        switch ($block->getType())
        {
            case 'adminhtml/template':
                if ($block->getTemplate() === 'system/config/js.phtml') { $this->regionHack($block, $transport); } 
                break;
            default:;
        }
    }    
    
    public function frontendBlockHtmlAfter($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ( ! $block) { return; }
        if ($block instanceof Mage_Checkout_Block_Total_Default) { $this->rowClass($block, $observer->getEvent()->getTransport()); }
    }    
    
    protected function rowClass(Mage_Core_Block_Abstract $block, Varien_Object $transport)
    {
        $document = new DOMDocument();
        $document->loadHTML($transport->getHtml());
        $xpath = new DOMXPath($document);
        $entries = $xpath->query('//tr');
        foreach ($entries as $entry)
        {
            $documentClasses = explode(' ', $entry->getAttribute('class'));
            $documentClasses[] = 'totals-row-' . $block->getTotal()->getCode();
            $documentClasses[] = 'totals-row-' . preg_replace('#[^a-z]+#', '', $block->getTotal()->getCode());
            $documentClasses = array_unique($documentClasses);
            $entry->setAttribute('class', implode(' ', $documentClasses));
        }        
        $transport->setHtml(preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $document->saveHTML())));
    }
}
?>