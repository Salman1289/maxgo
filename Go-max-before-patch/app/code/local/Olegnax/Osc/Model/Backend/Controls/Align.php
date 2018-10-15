<?php 
class Olegnax_Osc_Model_Backend_Controls_Align
{    
    public function toOptionArray()
    {
        return array(array('value'=>'left',  'label'=>"Left"), array('value'=>'right', 'label'=>"Right"), array('value'=>'center','label'=>"Center"));
    }
}