<?php 
class Olegnax_Osc_Model_Backend_Controls_Weight
{
    public function toOptionArray()
    {
        return array( array('value'=>100, 'label'=>"100"), array('value'=>200, 'label'=>"200"), array('value'=>300, 'label'=>"300"), array('value'=>400, 'label'=>"400 (Normal)"), array('value'=>500, 'label'=>"500"), array('value'=>600, 'label'=>"600"), array('value'=>700, 'label'=>"700 (Bold)"), array('value'=>800, 'label'=>"800"), array('value'=>900, 'label'=>"900"));
    }
}