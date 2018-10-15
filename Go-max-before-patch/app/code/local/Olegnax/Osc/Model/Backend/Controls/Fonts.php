<?php
class Olegnax_Osc_Model_Backend_Controls_Fonts
{    
    private $gfonts = "default,Georgia,Palatino,Times New Roman,Arial,Arial Black,Comic Sans MS,Impact,Lucida Sans Unicode,Tahoma,Trebuchet MS,Verdana,Courier New,Lucida Console";
        
    public function toOptionArray()
    {
        $fontsCollection = explode(',', $this->gfonts);
        $fontOptions = array();
        foreach ($fontsCollection as $f )
        {
            $fontOptions[] = array('value' => $f, 'label' => $f);
        }
        return $fontOptions;
    }

}