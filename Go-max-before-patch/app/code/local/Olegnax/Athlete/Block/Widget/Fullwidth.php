<?php

class Olegnax_Athlete_Block_Widget_Fullwidth extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{

	public function getContent()
	{
		return base64_decode(parent::getContent());
	}

	/**
	 * Produces html
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		$this->setTemplate('olegnax/widgets/fullwidth.phtml');
		$html = '';
		$content = $this->getData('content');
		if (empty($content)) {
			return $html;
		}

		return parent::_toHtml();
	}
}