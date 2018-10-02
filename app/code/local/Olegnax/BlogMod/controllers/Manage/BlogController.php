<?php

require_once 'AW/Blog/controllers/Manage/BlogController.php';

class Olegnax_BlogMod_Manage_BlogController extends AW_Blog_Manage_BlogController
{
    public function saveAction()
	{
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('blog/post');
			if (isset($data['tags'])) {
				if ($this->getRequest()->getParam('id')) {
					$model->load($this->getRequest()->getParam('id'));
					$originalTags = explode(",", $model->getTags());
				} else {
					$originalTags = array();
				}

				$tags = explode(',', $data['tags']);
				array_walk($tags, 'trim');

				foreach ($tags as $key => $tag) {
					$tags[$key] = Mage::helper('blog')->convertSlashes($tag, 'forward');
				}
				$tags = array_unique($tags);

				$commonTags = array_intersect($tags, $originalTags);
				$removedTags = array_diff($originalTags, $commonTags);
				$addedTags = array_diff($tags, $commonTags);

				if (count($tags)) {
					$data['tags'] = trim(implode(',', $tags));
				} else {
					$data['tags'] = '';
				}
			}
			if (isset($data['stores'])) {
				if ($data['stores'][0] == 0) {
					unset($data['stores']);
					$data['stores'] = array();
					$stores = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();
					foreach ($stores as $store) {
						$data['stores'][] = $store->getId();
					}
				}
			}

			if (!isset($data['image_main'])) {
				$data['image_main'] = 0;
			}
			$files = array('image','image_retina','thumb','thumb_retina',);
			foreach ($files as $_file) {
				if(isset($_FILES[$_file]['name']) and (file_exists($_FILES[$_file]['tmp_name']))) {
					try {
						$uploader = new Varien_File_Uploader($_file);
						$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
						$uploader->setAllowRenameFiles(true);
						$uploader->setFilesDispersion(false);
						$path = Mage::getBaseDir('media') . DS.'olegnax/athlete/blog'.DS ;
						$result = $uploader->save($path, $_FILES[$_file]['name'] );
					}catch(Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . '  '. $path);
						Mage::getSingleton('adminhtml/session')->setFormData($data);
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						return;
					}
					$data[$_file] = 'olegnax/athlete/blog/'.$result['file'];
				}
				else {
					// handle delete image
					if(isset($data[$_file]['delete']) && $data[$_file]['delete'] == 1)
						$data[$_file] = '';
					else
						unset($data[$_file]);
				}
			}


			$model
				->setData($data)
				->setId($this->getRequest()->getParam('id'))
			;

			try {
				$format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
				if (isset($data['created_time']) && $data['created_time']) {
					$dateFrom = Mage::app()->getLocale()->date($data['created_time'], $format);
					$model->setCreatedTime(Mage::getModel('core/date')->gmtDate(null, $dateFrom->getTimestamp()));
					$model->setUpdateTime(Mage::getModel('core/date')->gmtDate());
				} else {
					$model->setCreatedTime(Mage::getModel('core/date')->gmtDate());
				}

				if ($this->getRequest()->getParam('user') == null) {
					$model
						->setUser(
							Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
								'admin/session'
							)->getUser()->getLastname()
						)
						->setUpdateUser(
							Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
								'admin/session'
							)->getUser()->getLastname()
						)
					;
				} else {
					$model
						->setUpdateUser(
							Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
								'admin/session'
							)->getUser()->getLastname()
						)
					;
				}

				$model->save();

				/* recount affected tags */
				if (isset($data['stores'])) {
					$stores = $data['stores'];
				} else {
					$stores = array(null);
				}

				$affectedTags = array_merge($addedTags, $removedTags);

				foreach ($affectedTags as $tag) {
					foreach ($stores as $store) {
						if (trim($tag)) {
							Mage::getModel('blog/tag')->loadByName($tag, $store)->refreshCount();
						}
					}
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('blog')->__('Post was successfully saved')
				);
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find post to save'));
		$this->_redirect('*/*/');
	}

}
