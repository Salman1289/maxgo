<?php
/**
 * @version   1.0 12.0.2012
 * @author    Olegnax http://www.olegnax.com <mail@olegnax.com>
 * @copyright Copyright (C) 2010 - 2012 Olegnax
 */

class Olegnax_Athlete_Model_Config_Header_Type
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'slim-header',
	            'label' => Mage::helper('athlete')->__('slim-header')),
	        array(
	            'value'=>'',
	            'label' => Mage::helper('athlete')->__('normal-header')),
        );
    }

}