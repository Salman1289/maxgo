<?php
class Olegnax_Athlete_Block_Product_List_Sale extends Mage_Catalog_Block_Product_List
{
	protected $_cacheKeyArray;
	protected $_productsCount = null;
	protected $_productColumns = null;
	protected $_blockTitle = null;

	const DEFAULT_PRODUCTS_COUNT = 6;
	const DEFAULT_PRODUCT_COLUMNS = 4;
	const DEFAULT_PRODUCT_COLUMNS_MIN = 2;
	const DEFAULT_PRODUCT_COLUMNS_MAX = 7;

	protected function _construct()
	{
		$this->addData(array(
			'cache_lifetime' => 3600*24*30,
			'cache_tags'     => array('olegnax_athlete_product_list_sale'),
		));
	}

	public function getCacheKeyInfo()
	{
		if (NULL === $this->_cacheKeyArray)
		{
			$this->_cacheKeyArray = array(
                'OLEGNAX_ATHLETE_LIST_SALE',
				Mage::app()->getStore()->getId(),
				Mage::getDesign()->getPackageName(),
				Mage::getDesign()->getTheme('template'),
				Mage::app()->getStore()->getCurrentCurrencyCode(),
				(int)Mage::app()->getStore()->isCurrentlySecure(),
				$this->getCategoryId(),
				$this->getProductsCount(),
				$this->getBlockTitle(),
				$this->getBlockTitleSize(),
				$this->getProductColumns(),
				$this->getIsRandom(),
				$this->getTemplate(),
			);
		}
		return $this->_cacheKeyArray;
	}

	/**
	 * apply parameters from cms block
	 *
	 * Available options
	 * category_id
	 * products_count
	 * block_title
	 * block_title_size
	 * product_columns
	 * is_random
	 *
	 * Retrieve loaded category collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
            $collection = Mage::getResourceModel('catalog/product_collection');
			Mage::getModel('catalog/layer')->prepareProductCollection($collection);
            
            $todayStartOfDayDate  = Mage::app()->getLocale()->date()
                ->setTime('00:00:00')
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $todayEndOfDayDate  = Mage::app()->getLocale()->date()
                ->setTime('23:59:59')
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                
             $collection
               ->addAttributeToFilter('special_from_date', array('or'=> array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('special_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('special_price', array('gt'=> 0))
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'special_price', 'is'=>new Zend_Db_Expr('not null')),
                    )
                  )
            ;
            
			$collection->addStoreFilter();
			$isRandom = $this->getIsRandom();
			if ($isRandom)
				$collection->getSelect()->order('rand()');
            else
                $collection->addAttributeToSort('updated_at', 'desc');
			$productsCount = $this->getProductsCount();
			$collection->setPage(1, $productsCount)->load();
			$this->_productCollection = $collection;
		}
		return $this->_productCollection;
	}

	/**
	 * Get block title
	 *
	 * @return string
	 */
	public function getBlockTitle()
	{
		$this->_blockTitle = $this->getData('block_title');
		if (empty($this->_blockTitle)) {
			$this->_blockTitle = '';
		}
		return $this->_blockTitle;
	}

	/**
	 * Get number of products to be displayed
	 *
	 * @return int
	 */
	public function getProductsCount()
	{
		$this->_productsCount = $this->getData('products_count');
		if (empty($this->_productsCount)) {
			$this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
		}
		return $this->_productsCount;
	}

	/**
	 * Get product columns
	 *
	 * @return int
	 */
	public function getProductColumns()
	{
		$this->_productColumns = intval($this->getData('product_columns'));
		if ( $this->_productColumns < self::DEFAULT_PRODUCT_COLUMNS_MIN || $this->_productColumns > self::DEFAULT_PRODUCT_COLUMNS_MAX ) {
			$this->_productColumns = self::DEFAULT_PRODUCT_COLUMNS;
		}
		return $this->_productColumns;
	}



}
