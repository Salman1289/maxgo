<?php
class Olegnax_Colorswatches_Block_Swatches extends Mage_Core_Block_Template
{
	public function isSwatchesEnabled()
	{
		return $this->helper('olegnaxcolorswatches')->getCfg('main/status');
	}

    public function getSwatches()
	{
		return $this->helper('olegnaxcolorswatches')->getSwatches();
	}

	public function getSwatchKeys()
    {
		return $this->helper('olegnaxcolorswatches')->getSwatchKeys();
    }
	
}