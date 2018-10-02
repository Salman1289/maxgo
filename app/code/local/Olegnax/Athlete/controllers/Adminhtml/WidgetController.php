<?php
require_once( Mage::getModuleDir('controllers','Mage_Widget').DS.'Adminhtml'.DS.'WidgetController.php' );

class Olegnax_Athlete_Adminhtml_WidgetController extends Mage_Widget_Adminhtml_WidgetController
{
	public function buildWidgetAction()
	{
		$type = $this->getRequest()->getPost('widget_type');
		$params = $this->getRequest()->getPost('parameters', array());

		if ('athlete/widget_fullwidth' == $type) {
			$params['content'] = base64_encode($params['content']);
		}

		$asIs = $this->getRequest()->getPost('as_is');
		$html = Mage::getSingleton('widget/widget')->getWidgetDeclaration($type, $params, $asIs);
		$this->getResponse()->setBody($html);
	}
}