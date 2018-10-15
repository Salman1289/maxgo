<?php

class Olegnax_Athlete_Block_Widget_Form_Textarea extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
	/**
	 * Render element.
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$element->setValue(base64_decode($element->getValue()));
		$editor = new Varien_Data_Form_Element_Textarea($element->getData());

		$editor->setId($element->getId());
		$editor->setForm($element->getForm());

		return parent::render($editor);
	}
}