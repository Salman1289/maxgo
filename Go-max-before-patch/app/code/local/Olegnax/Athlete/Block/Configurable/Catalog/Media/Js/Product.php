<?php

class Olegnax_Athlete_Block_Configurable_Catalog_Media_Js_Product extends Mage_ConfigurableSwatches_Block_Catalog_Media_Js_Product 
{    
    protected function _getImageSizes() 
	{
        return array('image', 'small_image');
    }    
}
