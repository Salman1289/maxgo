<?php
class Olegnax_Athlete_Block_Product_List_Mostviewed extends Mage_Catalog_Block_Product_List
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
			'cache_tags'     => array('olegnax_athlete_product_list_mostviewed'),
		));
	}

public function getCacheKeyInfo()
	{
		if (NULL === $this->_cacheKeyArray)
		{
			$this->_cacheKeyArray = array(
				Mage::app()->getStore()->getId(),
				Mage::getDesign()->getPackageName(),
				Mage::getDesign()->getTheme('template'),
				Mage::app()->getStore()->getCurrentCurrencyCode(),
				(int)Mage::app()->getStore()->isCurrentlySecure(),
				$this->getCategoryId(), // GP - added category filter
				$this->getProductsCount(),
				$this->getBlockTitle(),
				$this->getBlockTitleSize(),
				$this->getProductColumns(),
				$this->getTemplate(),
			);
		}
		return $this->_cacheKeyArray;
	}

	/**
	 * apply parameters from cms block
	 *
	 * Available options
	 * products_count
	 * block_title
	 * block_title_size
	 * product_columns
	 *
	 * Retrieve loaded category collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected function _getProductCollection()
	{
		$storeId = Mage::app()->getStore()->getId();
		$products = Mage::getResourceModel('reports/product_collection')
			->addAttributeToSelect('*')
			->addAttributeToSelect(array('name', 'price', 'small_image'))
			->setStoreId($storeId)
			->addStoreFilter($storeId)
			->addViewsCount();

		// GP - added category filter
		if ( $this->getCategoryId() ) {
			$category = Mage::getModel('catalog/category')->load($this->getCategoryId());
			$products->addCategoryFilter($category);
		}
		// END GP - added category filter

		$products->setPageSize( $this->getProductsCount() );
		$productFlatData = Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
		if($productFlatData == "1") {
			$products->getSelect()->joinLeft(
				array('flat' => 'catalog_product_flat_'.$storeId),
				"(e.entity_id = flat.entity_id ) ",
				array(
					'flat.name AS name','flat.small_image AS small_image','flat.price AS price','flat.special_price as special_price','flat.special_from_date AS special_from_date','flat.special_to_date AS special_to_date'
				)
			);
		}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);

		$this->_productCollection = $products;

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
