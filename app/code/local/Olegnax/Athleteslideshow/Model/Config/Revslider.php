<?php

/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */
class Olegnax_Athleteslideshow_Model_Config_Revslider
{

	public function toOptionArray()
	{
		$options = array();
		if (Mage::helper('athleteslideshow')->isRevoulutionActive())
		{
			$revSliderCollection = Mage::getModel('nwdrevslider/sliders')->getCollection()->getData();			
			if(!empty($revSliderCollection))
			{
				foreach($revSliderCollection as $slider)
				{
					$options[] = array( 'value' => $slider['alias'], 'label' => $slider['title'] . ' (' . $slider['alias'] . ')');
				}		
			}	
		}
		return $options;
	}

}

?>
