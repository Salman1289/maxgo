<?php
/**
 * @version   1.0 12.06.2014
 * @author    Olegnax http://www.olegnax.com <mail@olegnax.com>
 * @copyright Copyright (C) 2010 - 2014 Olegnax
 */

class Olegnax_Athlete_Model_Widget_Fullwidth_Type
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'fullwidth-large-padding', 'label' => Mage::helper('athlete')->__('Large padding')),
            array('value'=>'fullwidth-small-padding', 'label' => Mage::helper('athlete')->__('Small padding')),
        );
    }

}