<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_ConfigurableSwatches
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Olegnax_Colorswatches_Helper_Productlist
 */
class Olegnax_Colorswatches_Helper_Productimg extends Mage_Core_Helper_Abstract
{
    
    const SWATCH_LABEL_SUFFIX = '-swatch';
    
    const MEDIA_IMAGE_TYPE_BASE = 'base_image';
    const MEDIA_IMAGE_TYPE_SMALL = 'small_image';
    
    /**
     * This array stores product images and separates them:
     * One group keyed by labels that match attribute values, another for all other images
     *
     * @var array
     */
    protected $_productImagesByLabel = array();

    /**
     * This array stores all possible labels and swatch labels used for associating gallery
     * images with swatches and main image swaps. It's use is for filtering the image gallery.
     *
     * @var array
     */
    protected $_productImageFilters = array();
    
    public function getGlobalSwatchUrl($object, $value)
    {
        $swatches = Mage::helper('olegnaxcolorswatches')->getSwatches();
        $attributeName = '';
        if ($object instanceof Mage_Catalog_Model_Layer_Filter_Item)
        {
            $attribute = $object->getFilter()->getAttributeModel();
            $attributeName = $attribute->getFrontendLabel();
        }
        elseif ($object instanceof Mage_Catalog_Model_Product) {  // fallback for swatches loaded for product view
            $_swatchAttribute = Mage::helper('olegnaxcolorswatches/productlist')->getSwatchAttribute();
            $attributeName = $_swatchAttribute->getFrontendLabel();
        }
        $result = false;
        $helper = Mage::helper('olegnaxcolorswatches');
        $value = $helper->normalizeKey($value);
        $attributeName = $helper->normalizeKey($attributeName);
        foreach ($swatches as $swatch)
        {
            if ($helper->normalizeKey($swatch['key']) == $attributeName 
                && $helper->normalizeKey($swatch['value']) == $value
            )
            {
                $result = $swatch['img'];
                break;
            }
        }
        
        if ( ! $result)
            return null;
        
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . 'wysiwyg/olegnax/colorswatches/'
            . $result
        ;
    }
    
    public function getProductImgByLabel($text, $product, $type = null)
    {
        $this->indexProductImages($product);

        //Get the product's image array and prepare the text
        $images = $this->_productImagesByLabel[$product->getId()];
        $text = Olegnax_Colorswatches_Helper_Data::normalizeKey($text);

        $resultImages = array(
            'standard' => isset($images[$text]) ? $images[$text] : null,
            'swatch' => isset($images[$text . self::SWATCH_LABEL_SUFFIX]) ? $images[$text . self::SWATCH_LABEL_SUFFIX]
                : null,
        );

        if (!is_null($type) && array_key_exists($type, $resultImages)) {
            $image = $resultImages[$type];
        } else {
            $image = (!is_null($resultImages['swatch'])) ? $resultImages['swatch'] : $resultImages['standard'];
        }

        return $image;
    }

    /**
     * Create the separated index of product images
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array|null $preValues
     * @return Mage_ConfigurableSwatches_Helper_Data
     */
    public function indexProductImages($product, $preValues = null)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return; // we only index images on configurable products
        }

        if (!isset($this->_productImagesByLabel[$product->getId()])) {
            $images = array();
            $searchValues = array();

            if (!is_null($preValues) && is_array($preValues)) { // If a pre-defined list of valid values was passed
                $preValues = array_map('Olegnax_Colorswatches_Helper_Data::normalizeKey', $preValues);
                foreach ($preValues as $value) {
                    $searchValues[] = $value;
                }
            } else { // we get them from all config attributes if no pre-defined list is passed in
                $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

                // Collect valid values of image type attributes
                foreach ($attributes as $attribute) {
                    if (Mage::helper('olegnaxcolorswatches')->attrIsSwatchType($attribute->getAttributeId())) {
                        foreach ($attribute->getPrices() as $option) { // getPrices returns info on individual options
                            $searchValues[] = Olegnax_Colorswatches_Helper_Data::normalizeKey($option['label']);
                        }
                    }
                }
            }

            $mapping = $product->getChildAttributeLabelMapping();
            $mediaGallery = $product->getMediaGallery();
            $mediaGalleryImages = $product->getMediaGalleryImages();

            if (empty($mediaGallery['images']) || empty($mediaGalleryImages)) {
                $this->_productImagesByLabel[$product->getId()] = array();
                return; //nothing to do here
            }

            $imageHaystack = array_map(function ($value) {
                return Olegnax_Colorswatches_Helper_Data::normalizeKey($value['label']);
            }, $mediaGallery['images']);

            foreach ($searchValues as $label) {
                $imageKeys = array();
                $swatchLabel = $label . self::SWATCH_LABEL_SUFFIX;

                $imageKeys[$label] = array_search($label, $imageHaystack);
                if ($imageKeys[$label] === false) {
                    $imageKeys[$label] = array_search($mapping[$label]['default_label'], $imageHaystack);
                }

                $imageKeys[$swatchLabel] = array_search($swatchLabel, $imageHaystack);
                if ($imageKeys[$swatchLabel] === false) {
                    $imageKeys[$swatchLabel] = array_search(
                        $mapping[$label]['default_label'] . self::SWATCH_LABEL_SUFFIX, $imageHaystack
                    );
                }

                foreach ($imageKeys as $imageLabel => $imageKey) {
                    if ($imageKey !== false) {
                        $imageId = $mediaGallery['images'][$imageKey]['value_id'];
                        $images[$imageLabel] = $mediaGalleryImages->getItemById($imageId);
                    }
                }
            }
            $this->_productImagesByLabel[$product->getId()] = $images;
        }
    }
}
