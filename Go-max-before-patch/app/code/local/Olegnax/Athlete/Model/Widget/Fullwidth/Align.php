<?php
/**
 * @version   1.0 12.06.2014
 * @author    Olegnax http://www.olegnax.com <mail@olegnax.com>
 * @copyright Copyright (C) 2010 - 2014 Olegnax
 */

class Olegnax_Athlete_Model_Widget_Fullwidth_Align
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'a-left', 'label' => Mage::helper('athlete')->__('Left')),
            array('value'=>'a-center', 'label' => Mage::helper('athlete')->__('Center')),
            array('value'=>'a-right', 'label' => Mage::helper('athlete')->__('Right')),
        );
    }

}