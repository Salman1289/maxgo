<?php

class Olegnax_Colorswatches_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const CONFIG_PATH_LIST_SWATCH_ATTRIBUTE = 'olegnaxcolorswatches/main/product_list_attribute';
    const CONFIG_PATH_SWATCH_ATTRIBUTES = 'olegnaxcolorswatches/main/swatch_attributes';
    
    private $_swatches;

    protected $_enabled = null;
    protected $_configAttributeIds = null;

	/**
	 * Retrieve config value for store by path
	 *
	 * @param string $path
	 * @param string $section
	 * @param int $store
	 * @return mixed
	 */
	public function getCfg($path, $section = 'olegnaxcolorswatches', $store = NULL)
	{
		$module = Mage::app()->getRequest()->getModuleName();
		if ( $path == 'main/replace_image' && $module == 'oxajax' ) {
			return 0;
		} else
			return Mage::helper('olegnaxall')->getCfg($path, $section, $store);
	}

	public function switchTemplate()
	{
		$template = 'olegnax/colorswatches/media.phtml';
		if ( Mage::helper('athlete')->getCfg('images/zoom') == 'lightbox') {
			$template = 'olegnax/colorswatches/lightbox.phtml';
		}
		if ( Mage::helper('athlete')->getCfg('images/zoom') == 'cloudzoom') {
			$template = 'olegnax/colorswatches/cloudzoom.phtml';
		}
		return $template;
	}
    
    public function isEnabled()
	{
        if (is_null($this->_enabled)) {
            $this->_enabled = $this->getCfg('main/status');
        }
        return $this->_enabled;
	}
    
    /**
     * Get list of attributes that should use swatches
     *
     * @return array
     */
    public function getSwatchAttributeIds()
    {
        if (is_null($this->_configAttributeIds)) {
            $this->_configAttributeIds = explode(',', Mage::getStoreConfig(self::CONFIG_PATH_SWATCH_ATTRIBUTES));
        }
        return $this->_configAttributeIds;
    }

    /**
     * Determine if an attribute should be a swatch
     *
     * @param int|Mage_Eav_Model_Attribute $attr
     * @return bool
     */
    public function attrIsSwatchType($attr)
    {
        if ($attr instanceof Varien_Object) {
            $attr = $attr->getId();
        }
        $configAttrs = $this->getSwatchAttributeIds();
        return in_array($attr, $configAttrs);
    }
    
    public function getSwatches()
	{
		return $this->_parseSwatches(Mage::helper('olegnaxcolorswatches')->getCfg('main/swatch_images'));
	}

	public function getSwatchKeys()
	{
		$swatches = $this->_parseSwatches(Mage::helper('olegnaxcolorswatches')->getCfg('main/swatch_images'));
		$keys = array();
		foreach ($swatches as $_swatch) {
			if ( !in_array($_swatch['key'], $keys) )
				$keys[] = $_swatch['key'];
		}
		return $keys;
	}

	protected function _parseSwatches($s)
	{
		if ( !empty($this->_swatches) ) {
			return $this->_swatches;
		}
		$swatches = array();
		if ($s) {
			if (preg_match_all("/^(.*)\:(.*)=(.*)$/m", $s, $m, PREG_SET_ORDER)) {
				foreach ($m as $_ln)
					$swatches[] = array(
						'key' => trim($_ln[1]),
						'value' => trim($_ln[2]),
						'img' => trim($_ln[3])
					);
			}
		}
		$this->_swatches = $swatches;
		return $swatches;
	}
    
    /**
     * Trims and lower-cases strings used as array indexes in json and for string matching in a
     * multi-byte compatible way if the mbstring module is available.
     *
     * @param $key
     * @return string
     */
    public static function normalizeKey($key) {
        if (function_exists('mb_strtolower')) {
            return trim(mb_strtolower($key, 'UTF-8'));
        }
        return trim(strtolower($key));
    }
    
    /**
     * Return the formatted hyphenated string
     *
     * @param string $str
     * @return string
     */
    public function getHyphenatedString($str)
    {
        $result = false;
        if (function_exists('iconv')) {
            $result = @iconv('UTF-8', 'ASCII//TRANSLIT', $str); // will issue a notice on failure, we handle failure
        }

        if (!$result) {
            $result = dechex(crc32(self::normalizeKey($str)));
        }

        return preg_replace('/([^a-z0-9]+)/', '-', self::normalizeKey($result));
    }
    
	public function getInStockChildsOnly($_product, $attributeCode)
	{
		if($_product && $_product->getId() && $attributeCode)
		{
			$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $_product); 			
			$result = array();
			foreach($childProducts as $child)
			{		
				if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($child)->getIsInStock())
				{
					$result[$child->getData($attributeCode)] = strtolower($child->getAttributeText($attributeCode));					
				}
			}
			if(!empty($result))
			{
				return $result;
			}			
		}
		return null;
	}   
}