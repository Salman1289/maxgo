<?php

class Olegnax_Osc_Helper_Message extends Mage_GiftMessage_Helper_Message
{

	public function getInline($type, Varien_Object $entity, $dontDisplayContainer = false)
	{
		if (!in_array($type, array('onepage_checkout', 'multishipping_adress')) && !$this->isMessagesAvailable($type, $entity)
		)
		{
			return '';
		}

		return Mage::getSingleton('core/layout')->createBlock('giftmessage/message_inline')
						->setId('giftmessage_form_' . $this->_nextId++)
						->setDontDisplayContainer($dontDisplayContainer)
						->setEntity($entity)
						->setType($type)
						->setTemplate('olegnax_osc/giftmessage/inline.phtml')
						->toHtml();
	}

}
