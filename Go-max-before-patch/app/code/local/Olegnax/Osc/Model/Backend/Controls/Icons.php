<?php 
class Olegnax_Osc_Model_Backend_Controls_Icons
{
    public function toOptionArray()
    {
        return array( array('value'=>'white', 'label'=>"White"), array('value'=>'black', 'label'=>"Black"), array('value'=>'colored', 'label'=>"Colored (blue or brown depends on style)"), array('value'=>'disabled', 'label'=>"Disabled"));
    }

}